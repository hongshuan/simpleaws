<?php

include 'public/init.php';

use Aws\Sqs\SqsClient;

try {
    $credentials = array(
        'region' => 'us-west-2',
        'version' => 'latest',
        'credentials' => array(
<<<<<<< HEAD
            'key'    => 'AISUXYFKVOJ2CNBRJCWZ',
            'secret' => 'sWUVe0s/D1oCbWVxXL9P8riWpB4RunI2hytpIraf',
=======
<<<<<<< HEAD
            'key'    => 'AHIUVYWUOX2JCFRBKWEC',
            'secret' => 'sWUVe0s/Q1oDWjVxL9PX8riNiMB4unhI2yt8paIf',
=======
            'key'    => 'AISUXYFKVOJ2CHBRJCWZB',
            'secret' => 'sWUVe0s/u1oCbWVxXL9P8riWpB4RunI2hytpI8af',
>>>>>>> 0d66240a984398b92be1e00204b4552a81ab6174
>>>>>>> 514a0808a34ff63b9108d1db84e019458d3ec624
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
