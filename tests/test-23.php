<?php

include 'public/init.php';

use Aws\Sqs\SqsClient;

try {
    $credentials = array(
        'region' => 'us-west-2',
        'version' => 'latest',
        'credentials' => array(
            'key'    => 'ASIUXYFKVOPJCHRBJWCZ',
            'secret' => 'sWUVe0s/D1oCbWVxLm9P8riWpBR4unI2hytp8Iaf',
        )
    );

    $client = new SqsClient($credentials);


    $queueUrl = 'https://sqs.us-west-2.amazonaws.com/805476385770/sqs-notif';

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
