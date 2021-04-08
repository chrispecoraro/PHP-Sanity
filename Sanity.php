<?php
require_once '../vendor/autoload.php';

use Sanity\Client as SanityClient;

/**
 * Class Sanity
 */
class Sanity
{

    /**
     * Sanity constructor.
     * @param string $projectId
     * @param string $dataset
     * @param string $token
     * @param string $apiVersion
     */
    public function __construct(
        private string $projectId,
        private string $dataset,
        private string $token,
        private string $apiVersion
    )
    {
        $this->client = new SanityClient([
            'projectId' => $this->getProjectId(),
            'dataset' => $this->getDataset(),
            'token' => $this->getToken(),
            'apiVersion' => $this->getApiVersion(),
        ]);
    }

    /**
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->projectId;
    }

    /**
     * @param string $projectId
     */
    public function setProjectId(string $projectId): void
    {
        $this->projectId = $projectId;
    }

    /**
     * @return string
     */
    public function getDataset(): string
    {
        return $this->dataset;
    }

    /**
     * @param string $dataset
     */
    public function setDataset(string $dataset): void
    {
        $this->dataset = $dataset;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     */
    public function setApiVersion(string $apiVersion): void
    {
        $this->apiVersion = $apiVersion;
    }


    public function getBlocks($text)
    {
        $blocks = explode(
            "</p>", html_entity_decode(str_replace("<p>", "", $text))
        );
        $arr = [];
        foreach ($blocks as $block) {
            $arr[] = $this->makeBlock($block);
        }
        return $arr;
    }

    public function makeBlock($text)
    {
        return [
            "_key" => (string)rand(100000, 999999999),
            "_type" => "block",
            "children" => [
                [
                    "_key" => (string)rand(100000, 999999999),
                    "_type" => "span",
                    "marks" => [],
                    "text" => $text
                ]
            ],
            'markDefs' => [],
            "style" => "normal"
        ];
    }

    /**
     *
     * Given a image URL, upload this image into Sanity and attach to a document.
     *
     * @param $imageUrl
     * @param $sanityDocumentId
     * @param string $fieldName
     * @param string $imageType
     */
    public function attachImage($imageUrl, $sanityDocumentId, $fieldName = 'image', $imageType = 'image')
    {
        $img = file_get_contents($imageUrl);
        $filePath = explode("/",);
        $tempFile = tempname(sys_get_temp_dir(), $filePath);
        file_put_contents($tempFile, $img);
        $asset = $this->client->uploadAssetFromFile('image', $tempFile);

        try {
            $this->client->patch($sanityDocumentId)
                ->set([$fieldName =>
                        ['_type' => $imageType, 'asset' =>
                            ['_ref' => $asset['_id']]
                        ]
                    ]
                )
                ->commit();
        } catch (BaseException $error) {
            echo 'Failed to attach image:';
            var_dump($error);
        }
    }

    /**
     * @param string $schemaType
     * @param array $documentList
     * @param array|null $fieldNames
     * @return array
     */
    public function batchCreate(string $schemaType, array $documentList, ?array $fieldNames = null): array
    {
        $schemaTypeArray = [
            '_type' => $schemaType,
        ];
        $ids = [];
        foreach ($documentList as $document) {
            $createFields = is_array($document) ? array_merge($schemaTypeArray, $document) : array_merge($schemaTypeArray, (array)$document);
            $createdDocument = $this->client->create($createFields);
            usleep(50);
            $ids[] = $createdDocument['_id'];
        }
        return $ids;
    }

    /**
     * @param string $schemaType
     * @param string $fileName
     * @param array|null $fieldNames
     * @param string $fieldSeparator
     * @return array
     */
    public function batchCreateFromFile(string $schemaType, string $fileName, ?array $fieldNames = null, string $fieldSeparator = ','): array
    {
        $schemaTypeArray = [
            '_type' => $schemaType,
        ];
        $ids = [];
        $file = file($fileName);
        foreach ($file as $line) {
            $columns = str_getcsv($line, $fieldSeparator);
            $createFields = array_merge(array_combine($fieldNames, $columns), $schemaTypeArray);
            $createdDocument = $this->client->create($createFields);
            usleep(50);
            $ids[] = $createdDocument['_id'];
        }
        return $ids;
    }


    /**
     * @param string $schemaType
     * @param object|array $document
     * @param array|null $fieldNames
     * @return string
     */
    public function create(string $schemaType, object|array $document, ?array $fieldNames = null)
    {
        $createFields = is_array($document) ? $document : (array)$document;

        if (is_array($fieldNames)) {
            if (count($fieldNames) == 0) {
                return "FieldNames need to be specified or set to null";
            }

            $createFields = array_combine($fieldNames, $document);
        }
        $mergedFields = array_merge(['_type' => $schemaType], $createFields);
        $createdDocument = $this->client->create($mergedFields);
        usleep(50);
        return $createdDocument['_id'];
    }

    /**
     * @param string $schemaType
     * @param object|array $document
     * @param array|null $fieldNames
     * @param string $separator
     * @return string
     */
    public function createFromString(string $schemaType, object|array $document, ?array $fieldNames = null, $separator = ',')
    {
        $schemaTypeArray = [
            '_type' => $schemaType,
        ];
        $createFields = is_array($document) ? array_merge($schemaTypeArray, $document) : array_merge($schemaTypeArray, (array)$document);

        $createdDocument = $this->client->create($createFields);
        usleep(50);
        return $createdDocument['_id'];
    }

    /**
     *
     * Copies the $documentType's $sourceFields's value to the $targetField value.
     *
     * Example: $mySanity->replace('post','title','seoTitle')
     *
     * @param string $documentType
     * @param string $targetField
     * @param string $sourceField
     */
    public function copy(string $documentType, string $targetField, string $sourceField)
    {
        $documents = $this->client->fetch('*[_type=="' . $documentType . '"]');

        foreach ($documents as $document) {
            try {
                $this->client->patch($document['id'])
                    ->set([$targetField => $document[$sourceField]])
                    ->commit();
            } catch (BaseException $error) {
                echo 'The update failed:';
                var_dump($error);
            }
            // Don't overwhelm the API
            usleep(20);
        }
    }
}
