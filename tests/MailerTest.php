<?php
namespace ninjacto\yii2mailgun\test;

use \ninjacto\yii2mailgun\Mailer;

class MailerTest extends TestCase
{
    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'mailer' => $this->createTestEmailComponent()
            ]
        ]);
    }
    /**
     * @return Mailer test email component instance.
     */
    protected function createTestEmailComponent()
    {
        $component = new \ninjacto\yii2mailgun\Mailer();
        return $component;
    }

    // Tests :
    public function testGetMailgunMailer()
    {
        $mailer = \Yii::$app->mailer;
        $this->assertTrue(is_object($mailer->getMailgunMailer()), 'Unable to get mail mailer instance!');
    }
}