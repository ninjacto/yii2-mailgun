<?php
namespace owner\models;

use common\models\User;
use Parse\ParseCloud;
use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseUser;
use yii\base\Model;
use Yii;
use yii\web\HttpException;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $name;
    public $email;
    public $password;
    public $password_repeat;
    public $address;
    public $phone;

    private $_user;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'filter', 'filter' => 'trim'],
            ['name', 'required'],
            ['name', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'validateUnique'],

            ['password', 'required'],
            ['password_repeat', 'required'],
            ['password','compare'],
            ['password', 'string', 'min' => 6],

            ['address', 'filter', 'filter' => 'trim'],
            ['address', 'required'],
            ['address', 'string', 'min' => 2, 'max' => 255],

            ['phone', 'filter', 'filter' => 'trim'],
            ['phone', 'required'],
            ['phone', 'string', 'min' => 2, 'max' => 255],
        ];
    }

    /**
     * @return null
     * @throws HttpException
     * @throws \Exception
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new ParseUser();
            $user->set("name", $this->name);
            $user->set("phoneNumber", $this->phone);
            $user->set("address", $this->address);
            $user->set("username", $this->email);
            $user->set("email", $this->email);
            $user->set("password", $this->password);

            try {
                $user->signUp();
            } catch (ParseException $ex) {
                throw new HttpException($ex->getCode(), 'Signup returned an error: ' . $ex->getMessage());
            }

            // Login
            try {
                $user = new \common\models\ParseUser();
                $this->_user = $user->login($this->email, $this->password);
            } catch (ParseException $ex) {
                throw new HttpException($ex->getCode(), 'Login returned an error: ' . $ex->getMessage());
            }

            ParseCloud::run("makeOwner");

            return $this->getUser();
        }

        return null;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $user = new \common\models\ParseUser();
            $this->_user = $user->getUser($this->email);
        }

        return $this->_user;
    }

    public function validateUnique($attribute, $params)
    {
        $queryOne = ParseUser::query();
        $queryOne->equalTo('email', $this->$attribute);
        $queryTwo = ParseUser::query();
        $queryTwo->equalTo('username', $this->$attribute);

        $query = ParseQuery::orQueries([$queryOne, $queryTwo]);
        $query->equalTo('email', $this->$attribute);
        $result = $query->first(true);
        if (!empty($result)) {
            $this->addError($attribute, 'This email address has already been taken.');
        }
    }
}
