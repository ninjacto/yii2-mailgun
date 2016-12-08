<?php
namespace ninjacto\yii2mailgun\tests;

use Yii;
use yii\helpers\FileHelper;
use ninjacto\yii2mailgun\Mailer;
use ninjacto\yii2mailgun\Message;

/**
 * @group vendor
 * @group mail
 * @group swiftmailer
 */
class MessageTest extends TestCase
{
    /**
     * @var string test email address, which will be used as receiver for the messages.
     */
    protected $testEmailReceiver = 'someuser@somedomain.com';

    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'mailer' => $this->createTestEmailComponent()
            ]
        ]);
        $filePath = $this->getTestFilePath();
        if (!file_exists($filePath)) {
            FileHelper::createDirectory($filePath);
        }
    }

    public function tearDown()
    {
        $filePath = $this->getTestFilePath();
        if (file_exists($filePath)) {
            FileHelper::removeDirectory($filePath);
        }
    }

    /**
     * @return string test file path.
     */
    protected function getTestFilePath()
    {
        return Yii::getAlias('@ninjacto/yii2mailgun/test/runtime') . DIRECTORY_SEPARATOR . basename(get_class($this)) . '_' . getmypid();
    }

    /**
     * @return Mailer test email component instance.
     */
    protected function createTestEmailComponent()
    {
        $component = new Mailer([
            'useFileTransport' => true,
        ]);

        return $component;
    }

    /**
     * @return Message test message instance.
     */
    protected function createTestMessage()
    {
        return Yii::$app->get('mailer')->compose();
    }

    /**
     * Creates image file with given text.
     * @param  string $fileName file name.
     * @param  string $text text to be applied on image.
     * @return string image file full name.
     */
    protected function createImageFile($fileName = 'test.jpg', $text = 'Test Image')
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD lib required.');
        }
        $fileFullName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $fileName;
        $image = imagecreatetruecolor(120, 20);
        $textColor = imagecolorallocate($image, 233, 14, 91);
        imagestring($image, 1, 5, 5, $text, $textColor);
        imagejpeg($image, $fileFullName);
        imagedestroy($image);

        return $fileFullName;
    }

    /**
     * Finds the attachment object in the message.
     * @param  Message $message message instance
     * @return array.
     */
    protected function getAttachment(Message $message)
    {
        $messageParts = $message->getMailgunMessage()->getFiles();
        $attachment = null;
        if(isset($messageParts['attachment']) && !empty($messageParts['attachment']))
            $attachment = $messageParts['attachment'];

        return $attachment;
    }

    // Tests :
    public function testGetMailgunMessage()
    {
        $message = new Message();
        $this->assertTrue(is_object($message->getMailgunMessage()), 'Unable to get Mailgun message!');
    }

    /**
     * @depends testGetMailgunMessage
     */
    public function testSetGet()
    {
        $message = new Message();
        $subject = 'Test Subject';
        $message->setSubject($subject);
        $this->assertEquals($subject, $message->getSubject(), 'Unable to set subject!');
        $from = 'from@somedomain.com';
        $message->setFrom($from);
        $this->assertContains($from, array_keys($message->getFrom()), 'Unable to set from!');
        $replyTo = 'reply-to@somedomain.com';
        $message->setReplyTo($replyTo);
        $this->assertContains($replyTo, $message->getReplyTo(), 'Unable to set replyTo!');
        $to = 'someuser@somedomain.com';
        $message->setTo($to);
        $this->assertContains($to, array_keys($message->getTo()), 'Unable to set to!');
        $cc = 'ccuser@somedomain.com';
        $message->setCc($cc);
        $this->assertContains($cc, array_keys($message->getCc()), 'Unable to set cc!');
        $bcc = 'bccuser@somedomain.com';
        $message->setBcc($bcc);
        $this->assertContains($bcc, array_keys($message->getBcc()), 'Unable to set bcc!');
    }

    /**
     * @depends testGetMailgunMessage
     */
    public function testSetupHeaderShortcuts()
    {
        $subject = 'Test Subject';
        $from = 'from@somedomain.com';
        $replyTo = 'reply-to@somedomain.com';
        $to = 'someuser@somedomain.com';
        $cc = 'ccuser@somedomain.com';
        $bcc = 'bccuser@somedomain.com';
        $returnPath = 'bounce@somedomain.com';
        $readReceiptTo = 'notify@somedomain.com';
        $messageString = $this->createTestMessage()
            ->setSubject($subject)
            ->setFrom($from)
            ->setReplyTo($replyTo)
            ->setTo($to)
            ->setCc($cc)
            ->setBcc($bcc)
            ->toString();
        $this->assertContains('Subject: ' . $subject, $messageString, 'Incorrect "Subject" header!');
        $this->assertContains('From: ' . $from, $messageString, 'Incorrect "From" header!');
        $this->assertContains('Reply-To: ' . $replyTo, $messageString, 'Incorrect "Reply-To" header!');
        $this->assertContains('To: ' . $to, $messageString, 'Incorrect "To" header!');
        $this->assertContains('Cc: ' . $cc, $messageString, 'Incorrect "Cc" header!');
        $this->assertContains('Bcc: ' . $bcc, $messageString, 'Incorrect "Bcc" header!');
    }

    /**
     * @depends testGetMailgunMessage
     */
    public function testSend()
    {
        $message = $this->createTestMessage();
        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Mailgun Test');
        $message->setTextBody('Yii Mailgun Test body');
        $this->assertTrue($message->send());
    }

    /**
     * @depends testSend
     */
    public function testAttachFile()
    {
        $message = $this->createTestMessage();
        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Mailgun Attach File Test');
        $message->setTextBody('Yii Mailgun Attach File Test body');
        $fileName = __FILE__;
        $message->attach($fileName);
        $this->assertTrue($message->send());
        $attachment = $this->getAttachment($message);
        $this->assertTrue(is_array($attachment), 'No attachment found!');
        if(isset($attachment['attachment']))
        $this->assertContains($attachment['attachment'], $fileName, 'Invalid file name!');
    }

    /**
     * @depends testSend
     */
    public function testSendAlternativeBody()
    {
        $message = $this->createTestMessage();
        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Mailgun Alternative Body Test');
        $message->setHtmlBody('<b>Yii Mailgun</b> test HTML body');
        $message->setTextBody('Yii Mailgun test plain text body');
        $this->assertTrue($message->send());
        $messageParts = $message->getMailgunMessage()->getMessage();
        $textPresent = false;
        $htmlPresent = false;


        $this->assertTrue($textPresent, 'No text!');
        $this->assertTrue($htmlPresent, 'No HTML!');
    }

    /**
     * @depends testGetMailgunMessage
     */
    public function testSerialize()
    {
        $message = $this->createTestMessage();
        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Mailgun Alternative Body Test');
        $message->setTextBody('Yii Mailgun test plain text body');
        $serializedMessage = serialize($message);
        $this->assertNotEmpty($serializedMessage, 'Unable to serialize message!');
        $unserializedMessaage = unserialize($serializedMessage);
        $this->assertEquals($message, $unserializedMessaage, 'Unable to unserialize message!');
    }

    /**
     * @depends testSendAlternativeBody
     */
    public function testAlternativeBodyCharset()
    {
        $message = $this->createTestMessage();
        $charset = 'windows-1251';
        $message->setCharset($charset);
        $message->setTextBody('some text');
        $message->setHtmlBody('some html');
        $content = $message->toString();
        $this->assertEquals(2, substr_count($content, $charset), 'Wrong charset for alternative body.');
        $message->setTextBody('some text override');
        $content = $message->toString();
        $this->assertEquals(2, substr_count($content, $charset), 'Wrong charset for alternative body override.');
    }

    /**
     * @depends testGetMailgunMessage
     */
    public function testSetupHeaders()
    {
        $messageString = $this->createTestMessage()
            ->addHeader('Some', 'foo')
            ->addHeader('Multiple', 'value1')
            ->addHeader('Multiple', 'value2')
            ->toString();
        $this->assertContains('Some: foo', $messageString, 'Unable to add header!');
        $this->assertContains('Multiple: value1', $messageString, 'First value of multiple header lost!');
        $this->assertContains('Multiple: value2', $messageString, 'Second value of multiple header lost!');
        $messageString = $this->createTestMessage()
            ->addHeader('Some', 'foo')
            ->addHeader('Some', 'override')
            ->addHeader('Multiple', ['value1', 'value2'])
            ->toString();
        $this->assertContains('Some: override', $messageString, 'Unable to set header!');
        $this->assertNotContains('Some: foo', $messageString, 'Unable to override header!');
        $this->assertContains('Multiple: value1', $messageString, 'First value of multiple header lost!');
        $this->assertContains('Multiple: value2', $messageString, 'Second value of multiple header lost!');
    }
}