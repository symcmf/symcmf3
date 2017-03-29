<?php
namespace PageBundle;
use \FunctionalTester;

class ArticleTestsCest
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

    public function testNewArticle(FunctionalTester $I)
    {
        $I->am('admin');
        $I->wantTo('create new article');

        $I->amOnRoute('easyadmin', ['entity' => 'Article', 'action' => 'new']);

        $I->fillField('Title', 'testArticle');
        $I->fillField('Content', 'testArticle');
        $I->click(['css' => 'button[type="submit"]']);

        $I->see('testArticle');
    }

    public function testNewArticleWithoutTitle(FunctionalTester $I)
    {
        $I->am('admin');
        $I->wantTo('create new article without title');

        $I->amOnRoute('easyadmin', ['entity' => 'Article', 'action' => 'new']);

        $I->fillField('Title', '');
        $I->fillField('Content', 'testArticle');
        $I->click(['css' => 'button[type="submit"]']);

        $I->see('This value should not be blank');
    }

    public function testNewArticleWithoutContent(FunctionalTester $I)
    {
        $I->am('admin');
        $I->wantTo('create new article without content');

        $I->amOnRoute('easyadmin', ['entity' => 'Article', 'action' => 'new']);

        $I->fillField('Title', 'test');
        $I->fillField('Content', '');
        $I->click(['css' => 'button[type="submit"]']);

        $I->see('This value should not be blank');
    }

    public function testNewArticleDuplicateTitle(FunctionalTester $I)
    {
        $I->am('admin');
        $I->wantTo('create new article without existed title');

        $this->testNewArticle($I);

        $I->amOnRoute('easyadmin', ['entity' => 'Article', 'action' => 'new']);

        $I->fillField('Title', 'testArticle');
        $I->fillField('Content', 'testArticle');
        $I->click(['css' => 'button[type="submit"]']);

        $I->see('This title is already in use');
    }
}
