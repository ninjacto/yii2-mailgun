<?php
namespace common\models;

use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use Parse\ParseException;

/**
 * ParseUser model
 *
 * @property string $id
 * @property string $username
 * @property string $email
 * @property string $auth_key
 * @property string $password write-only password
 */
class ParseUser implements IdentityInterface
{
    private $id = '';
    private $auth_key = '';
    public $name = '';
    public $email = '';
    public $parseObject = '';

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        try {
            $currentUser = \Parse\ParseUser::become($id);
            $user = new ParseUser();
            $user->parseObject = $currentUser;
            $user->name = $currentUser->get("name");
            $user->email = $currentUser->get("email");
            $session = \Yii::$app->session;
            if ($session->isActive)
                $session->open();

            $session->set('userId', $id);
            return $user;
        } catch (ParseException $ex) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = \Yii::$app->security->generateRandomString();
    }

    public function Login($username, $password)
    {
        $this->parseObject = \Parse\ParseUser::logIn($username, $password);
        $this->id = $this->parseObject->getSessionToken();
        return $this;
    }

    public function FbLogin($userId, $accessToken, $email=null, $name=null)
    {
        $this->parseObject = \Parse\ParseUser::logInWithFacebook($userId, $accessToken);
        if(!empty($email) || !empty($name)) {
            if(!empty($email))
                $this->parseObject->set("email", $email);
            if(!empty($name))
                $this->parseObject->set("name", $name);

            $this->parseObject->save();
        }
        $this->id = $this->parseObject->getSessionToken();
        return $this;
    }

    public function getUser($username)
    {
        $query = \Parse\ParseUser::query();
        $query->equalTo('email', $username);
        $this->parseObject = $query->first();
        $this->name = $this->parseObject->get("name");
        $this->email = $this->parseObject->get("email");
        return $this;
    }

    public function getFbUser($facebookId)
    {
        $query = \Parse\ParseUser::query();
        $query->equalTo('facebookId', $facebookId);
        $this->parseObject = $query->first();
        $this->name = $this->parseObject->get("name");
        $this->email = $this->parseObject->get("email");
        return $this;
    }
}