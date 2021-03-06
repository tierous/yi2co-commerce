<?php
namespace frontend\controllers;

use Yii;
//use common\models\LoginForm;
use yz\shoppingcart\ShoppingCart;
use common\models\CouponList;
use common\models\ProductCart;
use common\models\Comment;
use common\models\Customer;
use common\models\DeliveryAddress;
use frontend\models\CustomerLoginForm;
use frontend\models\MultipleAddToCartForm;
use common\models\Product;
use common\models\ProductAttribute;
use common\models\ProductOptions;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\models\Cart;
use common\models\CartList;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\Pagination;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public $layout = 'storenew';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
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

        $model = new CustomerLoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
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
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
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

    public function actionShop()
    {
        $query = Product::find()->where(['product_status' => 1]);
        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 12,
        ]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        //$model = Product::find()->where(['product_status' => 1])->all();

        return $this->render('shop', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    public function actionSingle($id)
    {
        $addComment = new Comment();
        $comment = Comment::find()->where(['product_id' => Yii::$app->getRequest()->getQueryParam('id')])->all();
        $attribute = ProductAttribute::find()->where(['product_id' => Yii::$app->getRequest()->getQueryParam('id')])->all();
        $options = ProductOptions::find()->where(['product_id' => Yii::$app->getRequest()->getQueryParam('id')])->all();
        $cart = new MultipleAddToCartForm();
        
        if (Yii::$app->request->post()) {
            $addComment->load(Yii::$app->request->post());
            $addComment->customer_id = Yii::$app->user->id;
            $addComment->product_id = Yii::$app->getRequest()->getQueryParam('id');
            $addComment->comment_date_added = new Expression('NOW()');
            $addComment->save();
            return $this->refresh();
        }
        return $this->render('single', [
            'model' => $this->findModel($id),
            'cart' => $cart,
            'attribute' => $attribute,
            'options' => $options,
            'comment' => $comment,
            'addComment' => $addComment,
        ]);
    }

    public function actionApplyCoupon($code)
    {
        $cart = Yii::$app->cart;
        $model = CouponList::find()
            ->where(['coupon_code' => $code])
            ->one();
        $discount = $model->coupon->coupon_discount;
        $cart->on(ShoppingCart::EVENT_COST_CALCULATION, function ($event) {
            $event->discountValue = $discount;
        });
        return $this->redirect(['cart']);
    }

    public function actionCart()
    {
        return $this->render('cart-view');
    }

    public function actionAddToCart($id)
    {
        $cart = Yii::$app->cart;

        $model = Product::findOne($id);
        if ($model) {
            $cart->put($model, 1);
            return $this->redirect(['cart']);
        }
        throw new NotFoundHttpException();
    }

    public function actionMultipleAddToCart($id, $quantity)
    {
        $cart = Yii::$app->cart;
        $quantity = $_POST['MultipleAddToCartForm']['quantity'];
        $model = Product::findOne($id);
        if ($model) {
            $cart->put($model, $quantity);
            return $this->redirect(['cart']);
        }
        throw new NotFoundHttpException();
    }

    public function actionRemoveFromCart($id)
    {
        $cart = Yii::$app->cart;

        $cart->removeById($id);
        return $this->redirect(['cart']);
    }

    public function actionAddQuantity($id, $quantity)
    {
        $cart = Yii::$app->cart;

        $model = Product::findOne($id);
        $quantity = $quantity+1;
        $cart->update($model, $quantity);
        return $this->redirect(['cart']);
    }

    public function actionRemoveQuantity($id, $quantity)
    {
        $cart = Yii::$app->cart;

        $model = Product::findOne($id);
        $quantity = $quantity-1;
        $cart->update($model, $quantity);
        return $this->redirect(['cart']);
    }

    public function actionCheckout()
    {
        $modelAddress = DeliveryAddress::find()->where(['customer_id' => Yii::$app->user->id])->all();
        $modelAddAddress = new DeliveryAddress();
        $model = Customer::findOne(Yii::$app->user->id);
        if ($model->load(Yii::$app->request->post())) {
            
        }
        return $this->render('checkout', [
            'model' => $model,
            'modelAddress' => $modelAddress,
            'modelAddAddress' => $modelAddAddress,
        ]);
    }

    public function getImageurl()
    {
      return \Yii::getAlias('@imageurl').'/'.$this->product_image;
    }

    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
