<?php
namespace ninjacto\yii2mailgun\test;

use ninjacto\yii2mailgun\Mailer;

class MailerTest extends TestCase
{
    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'email' => $this->createTestEmailComponent()
            ]
        ]);
    }
    /**
     * @return Mailer test email component instance.
     */
    protected function createTestEmailComponent()
    {
        $component = new Mailer();
        return $component;
    }
    // Tests :
    public function testGetMailgunMailer()
    {
        $mailer = new Mailer();
        $this->assertTrue(is_object($mailer->getMailgunMailer()), 'Unable to get mail mailer instance!');
    }
}