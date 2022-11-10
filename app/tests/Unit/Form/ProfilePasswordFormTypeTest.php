<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Form\ProfilePasswordFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProfilePasswordFormTypeTest extends TypeTestCase
{
    protected const FORM_GOOD_DATA = [
        'password' => 'old_password',
        'password_new' => ['first' => 'new_password', 'second' => 'new_password'],
    ];

    protected const FORM_BAD_DATA_NOT_MATCH = [
        'password' => 'old_password',
        'password_new' => ['first' => 'password_new', 'second' => 'new_password'],
    ];

    protected const FORM_BAD_DATA_SHORT = [
        'password' => 'old_password',
        'password_new' => ['first' => 'new', 'second' => 'new'],
    ];

    protected const FORM_BAD_DATA_EMPTY = [
        'password' => 'old_password',
        'password_new' => ['first' => '', 'second' => ''],
    ];

    public function testProfilePasswordForm(): void
    {
        $form = $this->factory->create(ProfilePasswordFormType::class);
        $form->submit(self::FORM_GOOD_DATA);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
    }

    public function testProfilePasswordFormPasswordNotMatch(): void
    {
        $form = $this->factory->create(ProfilePasswordFormType::class);
        $form->submit(self::FORM_BAD_DATA_NOT_MATCH);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());

        self::assertEquals(
            'my_profile.form.password.invalid_message',
            $form->get('password_new')->getErrors(true)[0]->getMessage()
        );
    }

    public function testProfilePasswordFormPasswordShort(): void
    {
        $form = $this->factory->create(ProfilePasswordFormType::class);
        $form->submit(self::FORM_BAD_DATA_SHORT);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());

        self::assertEquals(
            'This value is too short. It should have 6 characters or more.',
            $form->get('password_new')->getErrors(true)[0]->getMessage()
        );
    }

    public function testProfilePasswordFormPasswordEmpty(): void
    {
        $form = $this->factory->create(ProfilePasswordFormType::class);
        $form->submit(self::FORM_BAD_DATA_EMPTY);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());

        self::assertEquals(
            'This value should not be blank.',
            $form->get('password_new')->getErrors(true)[0]->getMessage()
        );
    }

    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }
}
