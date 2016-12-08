<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace ninjacto\yii2mailgun;

use Mailgun\Messages\MessageBuilder;
use yii\mail\BaseMessage;
use yii\mail\MessageInterface;

/**
 * Message implements a message class based on SwiftMailer.
 *
 * @see http://mailgunmailer.org/docs/messages.html
 * @see Mailer
 *
 * @method Mailer getMailer() returns mailer instance.
 *
 * @property array $headers Custom header values of the message. This property is write-only.
 * @property int $priority Priority value as integer in range: `1..5`, where 1 is the highest priority and
 * 5 is the lowest.
 * @property string $readReceiptTo Receipt receive email addresses. Note that the type of this property
 * differs in getter and setter. See [[getReadReceiptTo()]] and [[setReadReceiptTo()]] for details.
 * @property string $returnPath The bounce email address.
 * @property \Mailgun\Messages\MessageBuilder $mailgunMessage Swift message instance. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Message extends BaseMessage implements MessageInterface
{
    /**
     * @var MessageBuilder Mailgun message instance.
     */
    private $_mailgunMessage;

    /**
     * @return MessageBuilder Mailgun message instance.
     */
    public function getMailgunMessage()
    {
        if (!is_object($this->_mailgunMessage)) {
            $this->_mailgunMessage = $this->createMailgunMessage();
        }

        return $this->_mailgunMessage;
    }

    /**
     * @inheritdoc
     */
    public function setFrom($from)
    {
        $this->getMailgunMessage()->setFromAddress($from);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyTo)
    {
        $this->getMailgunMessage()->setReplyToAddress($replyTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTo($to)
    {
        $this->getMailgunMessage()->addToRecipient($to);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCc($cc)
    {
        $this->getMailgunMessage()->addCcRecipient($cc);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setBcc($bcc)
    {
        $this->getMailgunMessage()->addBccRecipient($bcc);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject)
    {
        $this->getMailgunMessage()->setSubject($subject);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text)
    {
        $this->setBody($text, 'text/plain');

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html)
    {
        $this->setBody($html, 'text/html');

        return $this;
    }

    /**
     * Sets the message body.
     * If body is already set and its content type matches given one, it will
     * be overridden, if content type miss match the multipart message will be composed.
     * @param string $body body content.
     * @param string $contentType body content type.
     */
    protected function setBody($body, $contentType)
    {
        $message = $this->getMailgunMessage();

        if ($contentType == 'text/html') {
            $message->setHtmlBody($body);
        } else {
            $message->setTextBody($body);
        }

    }

    /**
     * @inheritdoc
     */
    public function attach($fileName, array $options = [])
    {
        if (!empty($options['fileName'])) {
            $this->getMailgunMessage()->addAttachment($fileName, $options['fileName']);
        } else {
            $this->getMailgunMessage()->addAttachment($fileName);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function campaign($campaignId)
    {
        $this->getMailgunMessage()->addCampaignId($campaignId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function tag($tag)
    {
        $this->getMailgunMessage()->addTag($tag);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function parameter($name, $value)
    {
        $this->getMailgunMessage()->addCustomParameter($name, $value);

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function clickTracking($enable)
    {
        $this->getMailgunMessage()->setClickTracking($enable);
        return $this;
    }

    /**
     * Creates the Swift email message instance.
     * @return MessageBuilder email message instance.
     */
    protected function createMailgunMessage()
    {
        return new MessageBuilder();
    }

    /**
     * Adds custom header value to the message.
     * Several invocations of this method with the same name will add multiple header values.
     * @param string $name header name.
     * @param string $value header value.
     * @return $this self reference.
     * @since 2.0.6
     */
    public function addHeader($name, $value)
    {
        $this->getMailgunMessage()->addCustomHeader($name, $value);

        return $this;
    }

    /**
     * Sets custom header values to the message.
     * @param array $headers headers in format: `[name => value]`.
     * @return $this self reference.
     * @since 2.0.7
     */
    public function setHeaders($headers)
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * Returns the message sender.
     * @return array the sender
     */
    public function getFrom()
    {
        $message = $this->getMailgunMessage()->getMessage();

        return $message['from'];
    }

    /**
     * Returns the message recipient(s).
     * @return array the message recipients
     */
    public function getTo()
    {
        $message = $this->getMailgunMessage()->getMessage();

        return $message['to'];
    }

    /**
     * Returns the reply-to address of this message.
     * @return array the reply-to address of this message.
     */
    public function getReplyTo()
    {
        $message = $this->getMailgunMessage()->getMessage();

        return $message['h:reply-to'];
    }

    /**
     * Returns the Cc (additional copy receiver) addresses of this message.
     * @return array the Cc (additional copy receiver) addresses of this message.
     */
    public function getCc()
    {
        $message = $this->getMailgunMessage()->getMessage();

        return $message['cc'];
    }

    /**
     * Returns the Bcc (hidden copy receiver) addresses of this message.
     * @return array the Bcc (hidden copy receiver) addresses of this message.
     */
    public function getBcc()
    {
        $message = $this->getMailgunMessage()->getMessage();

        return $message['bcc'];
    }

    /**
     * Returns the message subject.
     * @return string the message subject
     */
    public function getSubject()
    {
        $message = $this->getMailgunMessage()->getMessage();

        return $message['subject'];
    }

    /**
     * Attach specified content as file for the email message.
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return $this self reference.
     */
    public function attachContent($content, array $options = [])
    {
        if (!empty($options['fileName'])) {
            $fileName = '/tmp/'.$options['fileName'];
            file_put_contents($fileName,$content);
            $this->getMailgunMessage()->addAttachment($fileName, $options['fileName']);
        } else {
            $fileName = '/tmp/'.uniqid('email_attachment_');
            file_put_contents('/tmp/'.uniqid('email_attachment_'),$content);
            $this->getMailgunMessage()->addAttachment($fileName);
        }

        return $this;
    }

    /**
     * Attach a file and return it's CID source.
     * This method should be used when embedding images or other data in a message.
     * @param string $fileName file name.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return string attachment CID.
     */
    public function embed($fileName, array $options = [])
    {
        if (!empty($options['fileName'])) {
            $this->getMailgunMessage()->addInlineImage($fileName, $options['fileName']);
        } else {
            $this->getMailgunMessage()->addInlineImage($fileName);
        }
    }

    /**
     * Attach a content as file and return it's CID source.
     * This method should be used when embedding images or other data in a message.
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return string attachment CID.
     */
    public function embedContent($content, array $options = [])
    {if (!empty($options['fileName'])) {
        $fileName = '/tmp/'.$options['fileName'];
        file_put_contents($fileName,$content);
        $this->getMailgunMessage()->addInlineImage($fileName, $options['fileName']);
    } else {
        $fileName = '/tmp/'.uniqid('email_attachment_');
        file_put_contents('/tmp/'.uniqid('email_attachment_'),$content);
        $this->getMailgunMessage()->addInlineImage($fileName);
    }

        return $this;
    }

    /**
     * Returns string representation of this message.
     * @return string the string representation of this message.
     */
    public function toString()
    {
        $message = $this->getMailgunMessage()->getMessage();

        foreach ($message as $title => $content) {
            if(is_array($content)) $content=implode(', ',$content);
            $output = ucfirst($title.': ').$content."\r\n";
        }
        return $output;
    }

    /**
     * Returns the character set of this message.
     * @return string the character set of this message.
     */
    public function getCharset()
    {
        $message = $this->getMailgunMessage()->getMessage();

        return $message['Content-Type'];
    }

    /**
     * Sets the character set of this message.
     * @param string $charset character set name.
     * @return $this self reference.
     */
    public function setCharset($charset)
    {
        $message = $this->getMailgunMessage()->getMessage();
        $message['Content-Type'] = 'text/html; charset=utf-8';
        $this->getMailgunMessage()->setMessage($message);
    }
}