<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Form\RestorePasswordChangeFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class RestorePasswordChangeFormTypeTest extends TypeTestCase
{
    protected const FORM_ERRORS = [
        'This value should not be blank.',
        'This value is too short. It should have 6 characters or more.',

        // Translate message when password do not mismatch.
        'restore_password.form_change_password.password.invalid_message',
    ];

    public function getSourceData(): \Generator
    {
        yield 'success' => [['password' => ['first' => 'password', 'second' => 'password']], true];

        yield 'fail password is too short' => [['password' => ['first' => 'abc', 'second' => 'abc']], false];

        yield 'fail password is blank' => [['password' => ['first' => '', 'second' => '']], false];

        yield 'fail password passwords do not match' => [['password' => ['first' => 'qwerty', 'second' => 'ytrewq']], false];
    }

    /**
     * @dataProvider getSourceData
     */
    public function testForm(array $repeatedFieldPassword, bool $isValid): void
    {
        $form = $this->factory->create(RestorePasswordChangeFormType::class);
        $form->submit($repeatedFieldPassword);

        $this->assertTrue($form->isSynchronized());

        if ($isValid) {
            self::assertTrue($form->isValid());
        } else {
            self::assertFalse($form->isValid());
            self::assertGreaterThan(0, $form->get('password')->getErrors(true)->count());

            foreach ($form->get('password')->getErrors(true) as $error) {
                self::assertTrue(\in_array($error->getMessage(), self::FORM_ERRORS));
            }

        }
    }

    public function testView(): void
    {
        $form = $this->factory->create(RestorePasswordChangeFormType::class);

        $fields = $form->createView()->children;

        self::assertEquals(['password', 'update'], array_keys($fields));
    }

    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }
}
