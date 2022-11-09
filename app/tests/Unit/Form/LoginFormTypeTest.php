<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Form\LoginFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class LoginFormTypeTest extends TypeTestCase
{
    protected const FORM_DATA = [
        '_username' => 'myLogin',
        '_password' => 'passWord',
    ];

    protected const FORM_DATA_BAD_EMPTY = [
        '_username' => '',
        '_password' => '',
    ];

    protected const FORM_DATA_BAD_SHORT = [
        '_username' => 'a',
        '_password' => 'a',
    ];

    public function testLoginForm(): void
    {
        $form = $this->factory->create(LoginFormType::class);
        $form->submit(self::FORM_DATA);

        self::assertTrue($form->isSynchronized());
        self::assertEquals(self::FORM_DATA, $form->getData());
    }

    public function testLoginFormEmptyData(): void
    {
        $form = $this->factory->create(LoginFormType::class);
        $form->submit(self::FORM_DATA_BAD_EMPTY);

        self::assertTrue($form->isSynchronized());

        self::assertFalse($form->isValid());
        self::assertStringContainsString('not be blank', $form->get('_username')->getErrors(true)[0]->getMessage());
        self::assertStringContainsString('not be blank', $form->get('_password')->getErrors(true)[0]->getMessage());
    }

    public function testLoginFormShortData(): void
    {
        $form = $this->factory->create(LoginFormType::class);
        $form->submit(self::FORM_DATA_BAD_SHORT);

        self::assertTrue($form->isSynchronized());

        self::assertFalse($form->isValid());
        self::assertStringContainsString(
            'This value is too short',
            $form->get('_username')->getErrors(true)[0]->getMessage()
        );
        self::assertStringContainsString(
            'This value is too short',
            $form->get('_password')->getErrors(true)[0]->getMessage()
        );
    }

    public function testLoginFormView(): void
    {
        $form = $this->factory->create(LoginFormType::class);
        $fields = $form->createView()->children;

        foreach (array_keys(self::FORM_DATA) as $item) {
            self::assertArrayHasKey($item, $fields);
        }
    }

    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
