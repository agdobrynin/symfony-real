<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Form\RestorePasswordFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class RestorePasswordFormTypeTest extends TypeTestCase
{
    public function getSourceData(): \Generator
    {
        yield 'success' => [['email' => 'good@domain.com'], true, null];

        yield 'fail empty' => [['email' => ''], false, ['This value should not be blank.']];

        yield 'fail short and invalid email' => [['email' => 'a@a'], false,
            [
                'This value is not a valid email address.',
                'This value is too short. It should have 5 characters or more.',
            ]
        ];

        yield 'fail invalid email' => [['email' => 'p@domain'], false, ['This value is not a valid email address.',]];
    }

    /**
     * @dataProvider getSourceData
     */
    public function testForm(array $data, bool $isValid, ?array $errorsMessages): void
    {
        $form = $this->factory->create(RestorePasswordFormType::class);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());

        if ($isValid) {
            self::assertTrue($form->isValid());
        } else {
            self::assertFalse($form->isValid());
            foreach ($form->get('email')->getErrors() as $error) {
                self::assertTrue(\in_array($error->getMessage(), $errorsMessages));
            }
        }
    }

    public function testView(): void
    {
        $form = $this->factory->create(RestorePasswordFormType::class);
        $fields = $form->createView()->children;

        self::assertEquals(['email', 'send'], array_keys($fields));
    }

    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
