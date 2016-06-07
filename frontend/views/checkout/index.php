<?php
    use yii\helpers\Html;
    use yii\bootstrap\ActiveForm;
    use yii\widgets\DetailView;
    use yii\helpers\Url;
    use yii\helpers\ArrayHelper;
    use common\models\DeliveryAddress;
    use common\models\City;
    use common\models\Province;
    use kartik\widgets\Select2;
?>
<!DOCTYPE html>
<html lang="en">
  <body>
    
    <div class="product-big-title-area">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="product-bit-title text-center">
                        <h2>Checkout</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="single-product-area">
        <div class="zigzag-bottom"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">Search Products</h2>
                        <form action="">
                            <input type="text" placeholder="Search products...">
                            <input type="submit" value="Search">
                        </form>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="product-content-right">
                        <div class="woocommerce">

                            

                                <h3 id="order_review_heading">Your order</h3>

                                <div id="order_review" style="position: relative;">
                                    <table class="shop_table">
                                        <thead>
                                            <tr>
                                                <th class="product-name">Product</th>
                                                <th class="product-total">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $i=1;
                                                foreach($data as $model):
                                                $sum =  $sum+ (($model['product_price'] + $model['product_options_price'] - (($model['product_price']+$model['product_options_price'])*($model['deal_discount']/100))) * $model['qty']);
                                                $save = $save+ ((($model['product_price']+$model['product_options_price']) * ($model['deal_discount']/100)) * $model['qty']);
                                            ?>
                                            <tr class="cart_item">
                                                <td class="product-name">
                                                    <?= $model['product_name'] ?> <?= $model['product_options_name'] ?> <?= $model['product_price']+$model['product_options_price'] ?><strong class="product-quantity"> × <?= $model['qty'] ?></strong>
                                                    <?php if($model['deal_category_id'] == 1){ ?>
                                                        <br>Discount -<?php echo (($model['product_price']+$model['product_options_price']) * ($model['deal_discount']/100)) * $model['qty'] ?>
                                                    <?php } ?>
                                                    <?php if($model['deal_category_id'] == 3 && $model['qty'] >= $model['deal_quantity_threeshold']){ ?>
                                                        <br>Discount -<?php echo (($model['product_price']+$model['product_options_price']) * ($model['deal_discount']/100)) * $model['qty'] ?>
                                                    <?php } ?>
                                                </td>
                                                <td class="product-total">
                                                    <!-- <?php if($model['deal_category_id'] == 3 && $model['qty'] < $model['deal_quantity_threeshold']) {?>
                                                    <span class="amount">Rp <?= ($model['product_price'] + $model['product_options_price']) * $model['qty'] ?></span> </td>
                                                    <?php } ?> -->
                                                    <span class="amount">Rp <?= (($model['product_price'] + $model['product_options_price']) - (($model['product_price']+$model['product_options_price'])*($model['deal_discount']/100))) * $model['qty'] ?></span> </td>
                                            </tr>
                                            <?php $i++;endforeach; ?>
                                        </tbody>
                                        <tfoot>

                                            <tr class="cart-subtotal">
                                                <th>Cart Subtotal</th>
                                                <td><span class="amount">Rp <?php echo $sum;?></span>
                                                </td>
                                            </tr>

                                            <?php if($save != NULL){ ?>
                                            <tr class="cart-save">
                                                <th>You Save</th>
                                                <td><span class="amount">Rp <?php echo $save;?></span>
                                                </td>
                                            </tr>
                                            <?php } ?>

                                            <?php foreach ($data as $model): ?>
                                            <?php if($model['deal_category_id'] == 2){ ?>
                                            <tr class="cart-get">
                                                <th>You Get</th>
                                                <td><span class="amount"> <?= $model['product_name'] ?><strong class="product-quantity"> × <?= $model['deal_quantity'] ?></strong></span>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                            <?php endforeach; ?>

                                            <?php if($coupondiscount != NULL) {?>
                                            <tr class="shipping">
                                                <th>Coupon Discount</th>
                                                <td>
                                                    <?php echo $coupondiscount; ?> %
                                                    <input type="hidden" class="shipping_method" value="free_shipping" id="shipping_method_0" data-index="0" name="shipping_method[0]">
                                                </td>
                                            </tr>
                                            <?php } ?>

                                            <tr class="order-total">
                                                <th>Order Total</th>
                                                <td><strong><span class="amount">Rp <?php echo $sum * ((100-$coupondiscount)/100) ?></span></strong> </td>
                                            </tr>

                                        </tfoot>
                                    </table>


                                    <div id="payment">

                                        <div class="form-row place-order">

                                        </div>

                                        <div class="clear"></div>

                                    </div>
                                </div>

                                <div id="customer_details" class="col2-set">
                                    <div class="col-1">
                                        <div class="woocommerce-billing-fields">
                                            <h3>Billing Details</h3>

                                            <!-- <?php $form = ActiveForm::begin(['class'=>'form-horizontal', 'action'=>Url::toRoute(['checkout/place-order'])]); ?>

                                            <?= $form->field($modelOrder, 'order_code')->textInput(['maxlength' => true]) ?>

                                            <?= $form->field($modelOrder, 'delivery_address_id')->textInput() ?>

                                            <div class="form-group">
                                                <?= Html::submitButton('Place Order', ['class' => 'btn btn-primary', 'name' => 'order-button']) ?>
                                                </div>

                                            <?php ActiveForm::end(); ?> -->

                                            <?php $form = ActiveForm::begin(['class'=>'form-horizontal', 'action'=>Url::toRoute(['checkout/place-order'])]); ?>

                                                <!-- <?php $i=1;foreach ($modelAddress as $address): ?>
                                                
                                                <?= $form->field($modelOrder, 'delivery_address_id')->radioList([$address->delivery_address_id=>$i]); $i++ ?>

                                                <?= DetailView::widget([
                                                    'model' => $address,
                                                    'attributes' => [
                                                        'delivery_address_name',
                                                        'delivery_address_address:ntext',
                                                    ],
                                                ]) ?>

                                                <?php endforeach;?> -->
                                                <?= Html::activeDropDownList($modelOrder, 'delivery_address_id',
                                                    ArrayHelper::map(DeliveryAddress::find()->where(['customer_id' => Yii::$app->user->id])->all(), 'delivery_address_id', 'delivery_address_address'))
                                                ?> <br><br>

                                                <!-- <?= $form->field($modelOrder, 'order_code')->textInput(['maxlength' => true]) ?> -->

                                                <div class="form-group">
                                                <?= Html::submitButton('Place Order', ['class' => 'btn btn-primary', 'name' => 'order-button']) ?>
                                                </div>

                                            <?php ActiveForm::end(); ?>
                                            <div class="clear"></div>

                                        </div>
                                    </div>

                                    <div class="col-2">
                                        <div class="woocommerce-shipping-fields">
                                            <h3 id="ship-to-different-address">
                        <label class="checkbox" for="ship-to-different-address-checkbox">Ship to a different address?</label>
                        </h3>
                                            <div class="shipping_address" style="display: block;">

                                                <?php $form = ActiveForm::begin(['class'=>'form-horizontal', 'action'=>Url::toRoute(['checkout/add-address', 'id'=>Yii::$app->user->id])]); ?>

                                                    <?= $form->field($modelAddAddress, 'delivery_address_name')->textInput() ?>

                                                    <?php
                                                        echo $form->field($modelAddAddress, 'city_id')->widget(Select2::classname(), [
                                                            'data' => ArrayHelper::map(City::find()->all(), 'city_id', 'city_name'),
                                                            'options' => ['placeholder' => 'Select a city ...'],
                                                            'pluginOptions' => [
                                                                'allowClear' => true
                                                            ],
                                                        ]);
                                                    ?>

                                                    <?= $form->field($modelAddAddress, 'delivery_address_address')->textarea(['rows' => 6]) ?>

                                                    <div class="form-group">
                                                        <?= Html::submitButton('Add Address', ['class' => 'btn btn-primary', 'name' => 'order-button']) ?>
                                                    </div>

                                                <?php ActiveForm::end(); ?>

                                                <div class="clear"></div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                
                            

                        </div>                       
                    </div>                    
                </div>
            </div>
        </div>
    </div>
  </body>
</html>