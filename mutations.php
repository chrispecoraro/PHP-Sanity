<?php
require_once('Sanity.php');
$sanity = new Sanity('qe2ul2l0', 'production', 'skpQwir3ptKek8Qsu7SeUz2J1hrL1AB2mJODQW2ixoj5TQhNkbBDhlFDt9fRpemsDoibvAdlMHs5DD4YtuJbgHU7A2NCLqb03hTLNxPtiPqUCgLXZWOdGk8t61aYSe1sttAmkEDgZuQxksQJ5LXrDhG2zmIrIf6km0gagLpRuHyYFBUNAqa8', '2021-03-25');
$posts = $sanity->fetch('*[_type=="socialPost"]');
foreach ($posts as $post) {

    $block = [
        "_ref" => "cbd106c7-46ce-46d0-b061-df62cdfafc75",
        "_type" => "reference"
    ];

    $post['socialPostType'] = $block;
    $sanity->client->createOrReplace($post);
    usleep(500);
}
