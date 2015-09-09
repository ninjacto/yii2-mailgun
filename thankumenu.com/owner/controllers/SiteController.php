<?php
namespace owner\controllers;

use owner\models\ProfileForm;
use Facebook\Facebook;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use owner\models\CheckRestaurantForm;
use owner\models\SubscribeForm;
use Parse\ParseCloud;
use Parse\ParseException;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Yii;
use common\models\LoginForm;
use owner\models\PasswordResetRequestForm;
use owner\models\ResetPasswordForm;
use owner\models\SignupForm;
use owner\models\ContactForm;
use yii\base\InvalidParamException;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\HttpException;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'login', 'signup', 'index', 'update', 'credit-card',
                    'claim-step-one', 'claim-step-two', 'claim-step-three',
                    'ajax-check', 'ajax-create', 'ajax-set-owner', 'logout', 'edit-user'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'signup', 'error', 'captcha'],
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'index', 'update', 'credit-card',
                            'claim-step-one', 'claim-step-two', 'claim-step-three',
                            'ajax-check', 'ajax-create', 'ajax-set-owner', 'logout', 'edit-user'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'ajaxCheck' => ['post'],
                    'ajaxCreate' => ['post'],
                    'ajaxSetOwner' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'foreColor' => 118974,
                'testLimit' => 1,
                'backColor' => 221221221,
                'transparent' => true
            ],
        ];
    }

    public function checkLogin() {
        $user = ParseUser::getCurrentUser();
        if ($user == null) {
            $user = Yii::$app->user->getIdentity();
            if ($user != null) {
                try {
                    return ParseUser::become($user->parseObject->getSessionToken());
                } catch (ParseException $ex) {
                    return Yii::$app->getResponse()->redirect(Yii::$app->user->loginUrl);
                }
            } else {
                return Yii::$app->getResponse()->redirect(Yii::$app->user->loginUrl);
            }
        }
        return $user;
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $this->checkLogin();
        $user = Yii::$app->user->getIdentity();
        $query = new ParseQuery("Restaurant");
        $query->equalTo("owner", $user->parseObject);
        $query->includeKey("owner");
        $results = $query->find();
        $output = [];
        foreach ($results as $restaurant) {
            $object = [
                'objectId' => $restaurant->getObjectId(),
                'name' => $restaurant->get('name'),
                'phone' => $restaurant->get('phoneNumber'),
                'address' => $restaurant->get('address'),
                'created_at' => $restaurant->getCreatedAt(),
                'plan' => $restaurant->get("plan"),
            ];
            $output[] = $object;
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $output,
            'sort' => [
                'attributes' => ['objectId', 'name', 'phone', 'address', 'plan'],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        \Yii::$app->layout = 'empty';
        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $fb = new Facebook([
                'app_id' => \Yii::$app->params['facebook']['appId'],
                'app_secret' => Yii::$app->params['facebook']['appSecret'],
                'default_graph_version' => 'v2.4',
            ]);
            $helper = $fb->getRedirectLoginHelper();
            $permissions = ['email', 'public_profile', 'user_friends']; // optional
            $fbLoginUrl = $helper->getLoginUrl(Url::toRoute(['site/fb-login'], true), $permissions);

            return $this->render('login', [
                'model' => $model,
                'fbLoginUrl' => $fbLoginUrl,
            ]);
        }
    }

    /**
     * Logs in a user with facebook.
     *
     * @return mixed
     */
    public function actionFbLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->FbLogin()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        ParseUser::logOut();
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success',
                    'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            $this->checkLogin();
            $user = Yii::$app->user->getIdentity();
            if($user->parseObject) {
                $model->name = $user->parseObject->get("name");
                $model->email = $user->parseObject->get("email");
            }
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {

        \Yii::$app->layout = 'empty';
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->checkLogin();

        ParseCloud::run("cancelSubscription", array("restaurantObjectId" => $id));

        return $this->redirect(['index']);
    }

    public function actionUpdate($id, $planId = null)
    {
        $this->checkLogin();
        $client = new Client([
            'headers' => [
                'X-Parse-Application-Id' => 'P8M1dl5bQlHnXGlk7m5GGJ2SDAZBIc91wJJHXIAt',
                'X-Parse-REST-API-Key'   => 'Ijvn7fgR1czr3wEsOlrKhzRnGtgUVYsCxAohtv5M',
            ]
        ]);
        try {
            $res = $client->get('https://api.parse.com/1/config');
            $configs = json_decode($res->getBody()->getContents(),true);
            $plans[] = [
                'id' => $configs['params']['PlanAId'],
                'image' => $configs['params']['PlanAImage']['url'],
                'name' => $configs['params']['PlanAName'],
                'price' => $configs['params']['PlanAPrice'],
            ];
            $plans[] = [
                'id' => $configs['params']['PlanBId'],
                'image' => $configs['params']['PlanBImage']['url'],
                'name' => $configs['params']['PlanBName'],
                'price' => $configs['params']['PlanBPrice'],
            ];
        } catch (RequestException $e) {
            throw new HttpException(400, $e->getMessage());
        }

        $query = new ParseQuery("Restaurant");
        try {
            $restaurant = $query->get($id);

            foreach($plans as $key=>$plan) {
                if($plan['id'] == $restaurant->get("plan")) {
                    $plan['selected'] = true;
                } else {
                    $plan['selected'] = false;
                }
                $plans[$key] = $plan;
                $planIds[]=$plan['id'];
            }

            if($planId !== null) {
                if(in_array($planId, $planIds)) {
                    try {
                        ParseCloud::run("subscription", ["restaurantObjectId" => $restaurant->getObjectId(), 'plan' => $planId]);
                        \Yii::$app->getSession()->setFlash('success', 'Subscription changed successfully, please use manager mobile app to edit your restaurant.');
                    } catch (ParseException $ex) {
                        \Yii::$app->getSession()->setFlash('error', $ex->getMessage());
                    }
                } else {
                    \Yii::$app->getSession()->setFlash('error', 'Plan does not recognized!');
                }

                $this->goHome();
            }
        } catch (ParseException $ex) {
            \Yii::$app->getSession()->setFlash('error', $ex->getMessage());
        }


        return $this->render('change_subscription', [
            'plans' => $plans,
            'restaurantId' => $id,
        ]);
    }

    /**
     * @return string
     * @throws HttpException
     * @var \common\models\ParseUser $user
     */
    public function actionCreditCard()
    {
        $this->checkLogin();
        $model = new SubscribeForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $this->checkLogin();
                $user = Yii::$app->user->getIdentity();
                if(!empty($user->parseObject->get("customerId")))
                    ParseCloud::run("updateCustomer", array("token" => $model->stripeToken));
                else
                    ParseCloud::run("createCustomer", array("token" => $model->stripeToken));

                \Yii::$app->getSession()->setFlash('success', 'Subscription changed successfully, please use manager mobile app to edit your restaurant.');
            } catch (ParseException $ex) {
                \Yii::$app->getSession()->setFlash('error', $ex->getMessage());
            }
            $this->goHome();
        }
        return $this->render('credit_card', [
            'model' => $model,
        ]);
    }

    /**
     * @return mixed
     * @throws HttpException
     */
    public function actionClaimStepOne()
    {
        $this->checkLogin();
        $model = new CheckRestaurantForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->phoneNumber = preg_replace("/[^0-9]/", "", $model->phoneNumber);
            $model->phoneNumberLastFive = substr($model->phoneNumber, -5);
            if (preg_match('/^\+\d(\d{3})(\d{3})(\d{4})$/', $model->phoneNumber, $matches)) {
                $model->phoneNumber = '(' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
            }
            $geocode = ParseCloud::run("geocode", array("address" => $model->address));

            $query = new ParseQuery("Restaurant");
            try {
                $query->equalTo("phoneNumberLastFive", $model->phoneNumberLastFive);
                $query->near("gps", $geocode['gps']);
                $query->includeKey("owner");
                $query->includeKey("displayImage");
                $first = $query->first();
                if (!empty($first)) {
                    $model->name = $first->get("name");
                    $model->displayImage = $first->get("displayImage")->get("thumbnailUrl");
                    $model->address = $first->get("address");
                    $model->phoneNumber = $first->get("phoneNumber");
                    $model->website = $first->get("website");
                    $model->objectId = $first->getObjectId();
                    if (!empty($first->get("owner"))) {
                        if ($first->get("owner")->getObjectId() == ParseUser::getCurrentUser()->getObjectId()) {
                            throw new HttpException(400, 'You are the owner of this restaurant!');
                        } else {
                            throw new HttpException(400,
                                'This restaurant is already claimed by someone else if you are the owner of this restaurant please contact us.');
                        }
                    }
                } else {
                    $model->displayImage = '/images/restaurant.jpg';
                    $model->address = $geocode['address'];
                    $model->objectId = false;
                }

                // store restaurant in session
                $session = Yii::$app->session;
                if ($session->isActive)
                    $session->open();
                $session->set('restaurant', [
                    'name' => $model->name,
                    'displayImage' => $model->displayImage,
                    'address' => $model->address,
                    'phoneNumber' => $model->phoneNumber,
                    'website' => $model->website,
                    'objectId' => $model->objectId,
                ]);

                return $this->redirect(['claim-step-two']);
            } catch (ParseException $ex) {
                throw new HttpException(500, 'Something went wrong: ' . $ex->getMessage());
            }
        } else {
            $session = Yii::$app->session;
            if ($session->has('restaurant')) {
                $model->attributes = $session->get('restaurant');
            }
        }
        $this->checkLogin();
        $user = Yii::$app->user->getIdentity();
        if(!empty($user->parseObject->get("customerId")))
            $isCustomer = true;
        else
            $isCustomer = false;
        return $this->render('claim_step_1', [
            'model' => $model,
            'isCustomer' => $isCustomer
        ]);
    }

    /**
     * @return mixed
     * @throws HttpException
     */
    public function actionClaimStepTwo()
    {
        $this->checkLogin();
        $client = new Client([
            'headers' => [
                'X-Parse-Application-Id' => 'P8M1dl5bQlHnXGlk7m5GGJ2SDAZBIc91wJJHXIAt',
                'X-Parse-REST-API-Key'   => 'Ijvn7fgR1czr3wEsOlrKhzRnGtgUVYsCxAohtv5M',
            ]
        ]);
        try {
            $res = $client->get('https://api.parse.com/1/config');
            $configs = json_decode($res->getBody()->getContents(),true);
            $plans[] = [
                'id' => $configs['params']['PlanAId'],
                'image' => $configs['params']['PlanAImage']['url'],
                'name' => $configs['params']['PlanAName'],
                'price' => $configs['params']['PlanAPrice'],
            ];
            $plans[] = [
                'id' => $configs['params']['PlanBId'],
                'image' => $configs['params']['PlanBImage']['url'],
                'name' => $configs['params']['PlanBName'],
                'price' => $configs['params']['PlanBPrice'],
            ];
        } catch (RequestException $e) {
            throw new HttpException(400, $e->getMessage());
        }

        $session = Yii::$app->session;
        if ($session->has('plan')) {
            foreach($plans as $key=>$plan) {
                if($plan['id'] == $session->get('plan')) {
                    $plan['selected'] = true;
                } else {
                    $plan['selected'] = false;
                }
                $plans[$key] = $plan;
                $planIds[]=$plan['id'];
            }
        }


        $this->checkLogin();
        $user = Yii::$app->user->getIdentity();
        if(!empty($user->parseObject->get("customerId")))
            $isCustomer = true;
        else
            $isCustomer = false;
        return $this->render('claim_step_2', [
            'plans' => $plans,
            'isCustomer' => $isCustomer
        ]);
    }

    /**
     * @param $plan
     * @return string
     * @throws HttpException
     * @var \common\models\ParseUser $user
     */
    public function actionClaimStepThree($plan)
    {
        $session = Yii::$app->session;
        $session->set('plan', $plan);

        $this->checkLogin();
        $user = Yii::$app->user->getIdentity();
        if(!empty($user->parseObject->get("customerId")))
            return $this->redirect(['claim-step-four']);

        $model = new SubscribeForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                ParseCloud::run("createCustomer", array("token" => $model->stripeToken));
                return $this->redirect(['claim-step-four']);
            } catch (ParseException $ex) {
                \Yii::$app->getSession()->setFlash('error', $ex->getMessage());
            }
        }
        return $this->render('claim_step_3', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     * @throws HttpException
     */
    public function actionClaimStepFour()
    {
        $session = Yii::$app->session;
        if ($session->has('restaurant')) {
            $restaurantData = $session->get('restaurant');
        } else {
            $this->redirect('/site/claim-step-one');
        }
        if ($session->has('plan')) {
            $plan = $session->get('plan');
        } else {
            $this->redirect('/site/claim-step-two');
        }

        $this->checkLogin();

        if (empty($restaurantData['objectId'])) {
            $restaurant = new ParseObject("Restaurant");

            $restaurant->set("name", $restaurantData['name']);
            $restaurant->set("address", $restaurantData['address']);
            $restaurant->set("phoneNumber", $restaurantData['phoneNumber']);
            if (!empty($restaurantData['website'])) {
                $restaurant->set("website", $restaurantData['website']);
            }

            try {
                $restaurant->save();
                $restaurantData['objectId'] = $restaurant->getObjectId();
            } catch (ParseException $ex) {
                \Yii::$app->getSession()->setFlash('error', $ex->getMessage());
            }
        }

        try {
            ParseCloud::run("subscription", ["restaurantObjectId" => $restaurantData['objectId'], 'plan' => $plan]);
            $session->remove('restaurant');
            $session->remove('plan');
            \Yii::$app->getSession()->setFlash('success', 'Subscription completed successfully, please use manager mobile app to edit your restaurant.');
        } catch (ParseException $ex) {
            \Yii::$app->getSession()->setFlash('error', $ex->getMessage());
        }

        $this->goHome();
    }

    /**
     * @return string
     * @throws HttpException
     */
    public function actionAjaxCheck()
    {
        $data = Yii::$app->request->post();
        if (empty($data['name']) || empty($data['address']) || empty($data['phoneNumber'])) {
            throw new HttpException(400, 'Wrong arguments please fill all mandatory fields.');
        }
        $data['phoneNumber'] = preg_replace("/[^0-9]/", "", $data['phoneNumber']);
        $data['phoneNumberLastFive'] = substr($data['phoneNumber'], -5);
        if (preg_match('/^\+\d(\d{3})(\d{3})(\d{4})$/', $data['phoneNumber'], $matches)) {
            $data['phoneNumber'] = '(' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
        }
        $data['geocode'] = ParseCloud::run("geocode", array("address" => $data['address']));

        $query = new ParseQuery("Restaurant");
        try {
            $query->equalTo("phoneNumberLastFive", $data['phoneNumberLastFive']);
            $query->near("gps", $data['geocode']['gps']);
            $query->includeKey("owner");
            $query->includeKey("displayImage");
            $first = $query->first();
            if (!empty($first)) {
                $data['name'] = $first->get("name");
                $data['displayImage'] = $first->get("displayImage")->get("thumbnailUrl");
                $data['address'] = $first->get("address");
                $data['phoneNumber'] = $first->get("phoneNumber");
                $data['website'] = $first->get("website");
                $code = 200;
                $objectId = $first->getObjectId();
                if (!empty($first->get("owner"))) {
                    if ($first->get("owner")->getObjectId() == ParseUser::getCurrentUser()->getObjectId()) {
                        throw new HttpException(400, 'You are the owner of this restaurant!');
                    } else {
                        throw new HttpException(400,
                            'This restaurant is already claimed by someone else if you are the owner of this restaurant please contact us.');
                    }
                }
            } else {
                $data['displayImage'] = '/images/restaurant.jpg';
                $data['address'] = $data['geocode']['address'];
                $code = 203;
                $objectId = 0;
            }

            $session = Yii::$app->session;
            if (!$session->isActive)
                $session->open();
            $session->set('restaurant', [
                'name' => $data['name'],
                'displayImage' => $data['displayImage'],
                'address' => $data['address'],
                'phoneNumber' => $data['phoneNumber'],
                'website' => $data['website'],
                'objectId' => $objectId,
            ]);

        } catch (ParseException $ex) {
            throw new HttpException(500, 'Something went wrong: ' . $ex->getMessage());
        }

        return json_encode([
            'code' => $code,
            'body' => $this->renderPartial('check', ['model' => $data]),
            'objectId' => $objectId
        ]);
    }

    /**
     * @return string
     * @throws HttpException
     */
    public function actionEditUser()
    {
        $this->checkLogin();
        $user = Yii::$app->user->getIdentity();
        $model = new ProfileForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {

                $model->email = $user->parseObject->get("email");
                $model->update($user->parseObject->getObjectId());

                \Yii::$app->getSession()->setFlash('success', 'User information updated successfully.');
            } catch (ParseException $ex) {
                \Yii::$app->getSession()->setFlash('error', $ex->getMessage());
            }
            $this->goHome();
        } else {
            $model->name = $user->parseObject->get("name");
            $model->address = $user->parseObject->get("address");
            $model->phone = $user->parseObject->get("phoneNumber");
            $model->email = $user->parseObject->get("email");
        }
        return $this->render('edit_user', [
            'model' => $model,
        ]);
    }

}