#!/usr/bin/env php
<?php

if ( ! file_exists('.googlecodeauth') ) {
    echo "No .googlecodeuth found!\n";
    echo "Please create googlecodeuth in the form of USER:PASSWORD";
    exit(1);
}

$lines = preg_split('/[\r\n]/', file_get_contents('.googlecodeauth'));
$userpwd = null;
foreach ( $lines as $line ) {
    if ( preg_match("/^\s*([^:]+):(.+?)\s*$/", $line, $matches) ) {
        $userpwd = $matches[1] . ':' . $matches[2];
    }
}

if ( $userpwd === null ) {
    print("Google Code Auth file contains no information\n");
    exit(1);
}

$args = $argv;
array_shift($args);
array_unshift($args, $userpwd);
call_user_func_array('upload_file', $args);

function upload_file($userpwd, $projectName, $filePath, $summary, $labels = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_URL, 'https://' . $projectName . '.googlecode.com/files');
    curl_setopt($ch, CURLOPT_POST, true);
    $post = array(
        'filename'=>'@' . $filePath,
        'summary' => $summary,
    );
    if ( $labels !== null ) {
        $post['label'] = array();
        foreach ( explode(',', $labels) as $label ) {
            $post['label'][] = trim($label);
        }
    }
    curl_setopt_custom_postfields($ch, $post);
    curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
    $response = null;
    try {
        $response = curl_exec($ch);
    }
    catch (Exception $e) {
        echo $e->getMessage() . " { was exception! }\n";
    }
    if ( preg_match('/file uploaded successfully/i', $response) ) {
        echo "File " . basename($filePath) . " uploaded successfully.\n";
    } else {
        if ( preg_match('/file exists or exceeds/i', $response) ) {
            echo "File " . basename($filePath) . " likely already exists.\n";
            exit(2);
        } elseif ( preg_match('/client does not have permission/i', $response) ) {
            echo "File " . basename($filePath) . " could not be uploaded.\n";
            echo "Invalid auth information\n";
            exit(1);
        } else {
            exit(3);
            echo " [ $response ]\n";
            echo curl_error($ch) . " [error]\n";
            echo curl_errno($ch) . " [errno]\n";
        }
    }
}

function curl_setopt_custom_postfields($ch, $postfields, $headers = null) {
    $algos = hash_algos();
    $hashAlgo = null;
    foreach ( array('sha1', 'md5') as $preferred ) {
        if ( in_array($preferred, $algos) ) {
            $hashAlgo = $preferred;
            break;
        }
    }
    if ( $hashAlgo === null ) { list($hashAlgo) = $algos; }
    $boundary =
        '----------------------------' .  substr(hash($hashAlgo, 'cURL-php-multiple-value-same-key-support' . microtime()), 0, 12);

    $body = array();
    $crlf = "\r\n";
    $fields = array();
    foreach ( $postfields as $key => $value ) {
        if ( is_array($value) ) {
            foreach ( $value as $v ) {
                $fields[] = array($key, $v);
            }
        } else {
            $fields[] = array($key, $value);
        }
    }
    foreach ( $fields as $field ) {
        list($key, $value) = $field;
        if ( strpos($value, '@') === 0 ) {
            preg_match('/^@(.*?)$/', $value, $matches);
            list($dummy, $filename) = $matches;
            $body[] = '--' . $boundary;
            $body[] = 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($filename) . '"';
            $body[] = 'Content-Type: application/octet-stream';
            $body[] = '';
            $body[] = file_get_contents($filename);
        } else {
            $body[] = '--' . $boundary;
            $body[] = 'Content-Disposition: form-data; name="' . $key . '"';
            $body[] = '';
            $body[] = $value;
        }
    }
    $body[] = '--' . $boundary . '--';
    $body[] = '';
    $contentType = 'multipart/form-data; boundary=' . $boundary;
    $content = join($crlf, $body);
    $contentLength = strlen($content);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Length: ' . $contentLength,
        'Expect: 100-continue',
        'Content-Type: ' . $contentType,
    ));

    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);

}


?>
