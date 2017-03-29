<?php
namespace PageBundle;
use \FunctionalTester;

class CategoryTestsCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('_email', 'admin@mail.com');
        $I->fillField('_password', 'admin');
        $I->click(['css' => "button[type='submit']"]);
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function testNewCategory(FunctionalTester $I)
    {
        $I->am('admin');
        $I->wantTo('create new category');

        $I->amOnRoute('easyadmin', ['entity' => 'Category', 'action' => 'new']);
        $I->fillField('Name', 'testCategory');
        $I->click(['css' => 'button[type="submit"]']);
        $I->see('testCategory');
    }

    public function testWithoutName(FunctionalTester $I)
    {
        $I->am('admin');
        $I->wantTo('create new category without name');

        $I->amOnRoute('easyadmin', ['entity' => 'Category', 'action' => 'new']);
        $I->fillField('Name', '');
        $I->click(['css' => 'button[type="submit"]']);
        $I->see('This value should not be blank');
    }

    public function testWithoutDuplicateName(FunctionalTester $I)
    {
        $this->testNewCategory($I);

        $I->am('admin');
        $I->wantTo('create new category with existed name');

        $I->amOnRoute('easyadmin', ['entity' => 'Category', 'action' => 'new']);
        $I->fillField('Name', 'testCategory');
        $I->click(['css' => 'button[type="submit"]']);
        $I->see('This name of category is already in use');
    }
}
