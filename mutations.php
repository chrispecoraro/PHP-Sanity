<?php
require_once('Sanity.php');
$sanity = new Sanity('qe2ul2l0','production','skpQwir3ptKek8Qsu7SeUz2J1hrL1AB2mJODQW2ixoj5TQhNkbBDhlFDt9fRpemsDoibvAdlMHs5DD4YtuJbgHU7A2NCLqb03hTLNxPtiPqUCgLXZWOdGk8t61aYSe1sttAmkEDgZuQxksQJ5LXrDhG2zmIrIf6km0gagLpRuHyYFBUNAqa8','2021-03-25');

$pages = $sanity->all('academicProgramPage');
$cnt = 0;
foreach($pages as $page) {

   if ($page['school']['_ref']== 'bdbec5f6-65a3-4bc4-99ce-a1391c1886d8' && $page['slug']['current']!=='/master-of-arts-in-teaching'){

       echo $page['slug']['current']."\n";
        $page['_type'] = 'page';
        $page['_id'] = uniqid(more_entropy: true);
        $page['_rev'] = '';
        $newDocument = $sanity->client->create($page);
        var_dump($newDocument);

   }

}
