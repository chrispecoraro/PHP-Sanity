<?php

namespace Chrispecoraro\PhpSanity;

use Sanity\Client as SanityClient;

class PhpSanity
{
    /**
     * @var string
     */
    private string $documentId;

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
    public function getDocumentId(): string
    {
        return $this->documentId;
    }


    /**
     * @param string $documentId
     */
    public function setDocumentId(string $documentId): void
    {
        $this->documentId = $documentId;
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

    /**
     * @param $query
     * @return mixed
     */
    public function fetch($query)
    {
        return $this->client->fetch($query);
    }

    /**
     * @param string $schemaType
     * @param array $selectFields
     * @return array
     */
    public function all(string $schemaType, array $selectFields = []): array
    {
        $query = "*[_type == '$schemaType']";
        $selectFieldArray = [];
        if (count($selectFields) > 0) {
            foreach ($selectFields as $field) {
                $fieldArray = preg_split("/[^A-Za-z0-9]/",  $field);
                $fieldName = $this->getCamelCasedName($fieldArray, $field);
                array_push($selectFieldArray,"\"$fieldName\":$field");
            }
            $query.='{'. implode(',',$selectFieldArray).'}';
        }

        return $this->client->fetch($query);
    }

    /**
     * @param string $fieldName
     * @param null $relatedFieldId
     * @param null $documentId
     * @return $this
     */
    public function attach($fieldName, $relatedFieldId, $documentId)
    {
        try {
            $this->client->patch($this->useDocumentId($documentId))
                ->set([$fieldName =>
                            ['_ref' => $relatedFieldId,
                                '_type' => 'reference'
                            ]
                    ]
                )
                ->commit();
        } catch (BaseException $error) {
            echo 'Failed to attach document Id $documentId to field $fieldName:';
            var_dump($error);
        }
        return $this;
    }

    /**
     * @param $imageUrl
     * @param string $fieldName
     * @param string $imageType
     * @param null $documentId
     * @return $this
     */
    public function attachImage($imageUrl, $fieldName = 'image', $imageType = 'image', $documentId = null)
    {
        $img = file_get_contents($imageUrl);
        $filePath = explode("/",$imageUrl);
        $tempFile = tempnam(sys_get_temp_dir(), $filePath[count($filePath)-1]);
        file_put_contents($tempFile, $img);
        $asset = $this->client->uploadAssetFromFile('image', $tempFile);

        try {
            $this->client->patch($this->useDocumentId($documentId))
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
        return $this;
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
            if (is_array($fieldNames)) {
                if (count($fieldNames) == 0) {
                    return ["FieldNames need to be specified or set to null."];
                }
                $values = is_array($document) ? $document : [$document];
                $createFields = array_combine($fieldNames, $values);
            }
            $mergedFields = array_merge(['_type' => $schemaType], $createFields);

            $createdDocument = $this->client->create($mergedFields);
            $ids[] = $createdDocument['_id'];
            usleep(50);
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
     * @return mixed|string
     */
    public function create(string $schemaType, object|array $document, ?array $fieldNames = null)
    {
        $createFields = is_array($document) ? $document : (array)$document;

        if (is_array($fieldNames)) {
            if (count($fieldNames) == 0) {
                return "FieldNames need to be specified or set to null";
            }

            $createFields = array_combine($fieldNames, $createFields);
        }
        $mergedFields = array_merge(['_type' => $schemaType], $createFields);
        $createdDocument = $this->client->create($mergedFields);
        usleep(50);
        return $createdDocument['_id'];
    }

    /**
     * @param string $schemaType
     */
    public function deleteAll(string $schemaType)
    {
        $this->client->delete(
            ['query' =>
                '*[_type == "' . $schemaType . '"]'
            ],
        );
    }

    /**
     * @param string $id
     */
    public function deleteById(string $id)
    {
        $this->client->delete(
            [
                'query' => '*[_id == "' . $id . '"]'
            ],
        );
    }

    /**
     * @param string $schemaType
     * @param string $string
     * @param array|null $fieldNames
     * @param string $separator
     * @return mixed
     */
    public function createFromString(string $schemaType, string $string, ?array $fieldNames = null, $separator = ',')
    {
        $schemaTypeArray = [
            '_type' => $schemaType,
        ];
        $createFields = str_getcsv($string, $separator);
        $createFields = array_combine($fieldNames, $createFields);


        $createdDocument = $this->client->create(array_merge($schemaTypeArray, $createFields));

        usleep(50);
        return $createdDocument['_id'];
    }

    /**
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
            usleep(50);
        }
    }

    /**
     * @param $fieldName
     * @param $fieldValue
     * @param null $documentId
     * @return $this
     */
    public function set($fieldName, $fieldValue, $documentId = null)
    {
        $this->client->patch($this->useDocumentId($documentId))->set([$fieldName => $fieldValue])
            ->commit();
        return $this;
    }

    /**
     * @param $block
     * @return string
     */
    public function getStringFromBlock($block)
    {

        if (is_array($block)) {
            $string = array_map(function($block)
            {
                if ($block['_type'] !== 'block' || !isset($block['children'])) {
                    return '';
                }
                $childenTextBlocks = array_map(function($child)
                {
                    return $child['text'];
                }, $block['children']);
                return implode($childenTextBlocks);
            }, $block);
            return implode("\n\n", $string);
        }
        return '';
    }

    /**
     * @param mixed $documentId
     * @return mixed
     */
    protected function useDocumentId(mixed $documentId): mixed
    {
        $documentId = $documentId ?? $this->getDocumentId();
        return $documentId;
    }

    /**
     * @param array $fieldArray
     * @param string $field
     * @return string
     */
    public function getCamelCasedName(array $fieldArray, mixed $field): string
    {
        if (count($fieldArray) > 1) {
            return str_replace(' ', '', $fieldArray[0] . ucwords(implode(' ', array_slice($fieldArray, 1))));
        } 
        return $field;
    }
}
