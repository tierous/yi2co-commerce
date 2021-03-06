<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealCategory */

$this->title = 'Update Deal Category: ' . ' ' . $model->deal_category_name;
$this->params['breadcrumbs'][] = ['label' => 'Deal Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->deal_category_name, 'url' => ['view', 'id' => $model->deal_category_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="deal-category-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
