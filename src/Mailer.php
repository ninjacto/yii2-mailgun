<?php

namespace ninjacto\yii2mailgun;

use Yii;
use yii\mail\BaseMailer;
use Mailgun\Mailgun;

/**
 * Mailer implements a mailer based on Mailgun.
 *
 * To use Mailer, you should configure it in the application configuration like the following,
 *
 * ~~~
 * 'components' => [
 *     ...
 *     'mailer' => [
 *         'class' => 'ninjacto\yii2mailgun\Mailer',
 *         'domain' => 'example.com',
 *         'key' => 'key-somekey',
 *         'tags' => ['yii'],
 *         'enableTracking' => false,
 *     ],
 *     ...
 * ],
 * ~~~
 */
class Mailer extends BaseMailer
{

    /**
     * [$messageClass description]
     * @var string message default class name.
     */
    public $messageClass = 'ninjacto\yii2mailgun\Message';

    public $domain;
    public $key;

    public $fromAddress;
    public $fromName;
    public $tags = [];
    public $campaignId;
    public $enableDkim;
    public $enableTestMode;
    public $enableTracking;
    public $clicksTrackingMode; // true, false, "html"
    public $enableOpensTracking;

    private $_mailgunMailer;

    /**
     * @return Mailgun Mailgun mailer instance.
     */
    public function getMailgunMailer()
    {
        if (!is_object($this->_mailgunMailer)) {
            $this->_mailgunMailer = $this->createMailgunMailer();
        }

        return $this->_mailgunMailer;
    }

    /**
     * @param Message $message
     * @inheritdoc
     */
    protected function sendMessage($message)
    {
        $message->clickTracking($this->clicksTrackingMode)->tag($this->tags);

        Yii::info('Sending email', __METHOD__);

        $response = $this->getMailgunMailer()->post(
            "{$this->domain}/messages",
            $message->getMailgunMessage()->getMessage(),
            $message->getMailgunMessage()->getFiles()
        );

        Yii::info('Response : ' . print_r($response, true), __METHOD__);

        return true;
    }

    /**
     * Creates Mailgun mailer instance.
     * @return Mailgun mailer instance.
     */
    protected function createMailgunMailer()
    {
        $mgClient = new Mailgun($this->key);

        return $mgClient;
    }
}
