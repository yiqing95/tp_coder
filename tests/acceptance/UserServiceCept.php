<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('perform actions and see result');

// $I->amOnPage('/Api/v1/index');
/*
$I->sendAjaxGetRequest('/Api/v1/index',array(
   'method'=>'user.helloTo',
    'params'=>array(

    ),
));
*/


$I->see('yiqing');