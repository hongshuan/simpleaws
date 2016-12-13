<?php

include 'public/init.php';

use Aws\Sqs\SqsClient;

try {
    $credentials = array(
        'region' => 'us-west-1',
        'version' => 'latest',
        'credentials' => array(
            'key'    => 'AKIUXYFUVO2JEHRBJWEC',
            'secret' => 'sWUVe0s/D1oCbWVxL9XP8riNiMB4unIj2yt8pIaf',
        )
    );

    $client = new SqsClient($credentials);


    $queueUrl = 'https://sqs.us-west-1.amazonaws.com/805676389772/sqs-msg';

    $result = $client->receiveMessage(array(
        'QueueUrl' => $queueUrl,
        'MaxNumberOfMessages' => 10
    ));


    if ($result['Messages'] == null) {
        exit; // No message to process
    }

    $entries = [];

    // Get the message information
    foreach ($result['Messages'] as $message) {
        $messageId = $message['MessageId'];
        $md5Body = $message['MD5OfBody'];
        $receiptHandle = $message['ReceiptHandle'];
        $body = $message['Body'];
        $entries[] = [ 'Id' => $messageId, 'ReceiptHandle' => $receiptHandle ];
    }

    $result = $client->deleteMessageBatch([
        'QueueUrl' => $queueUrl,
        'Entries'  => $entries
    ]);

} catch (Exception $e) {
    die('Error receiving message to queue ' . $e->getMessage());
}
