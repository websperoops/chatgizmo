<?php
    require_once __DIR__.'yookassa-sdk-php-master/vendor/autoload.php';
    $source = file_get_contents("php://input");
    $requestBody = json_decode($source,true);
    use YooKassa\Model\Notification\NotificationSucceeded;
    use YooKassa\Model\Notification\NotificationWaitingForCapture;
    use YooKassa\Model\NotificationEventType;
    try {
        $notification = ($requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
          ? new NotificationSucceeded($requestBody)
          : new NotificationWaitingForCapture($requestBody);
    } catch (Exception $e) {
        // Processing errors
    }
    $payment = $notification->getObject();
    $id = $payment->id;
?>