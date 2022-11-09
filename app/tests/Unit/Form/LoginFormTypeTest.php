<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Form\LoginFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class LoginFormTypeTest extends TypeTestCase
{
    protected const FORM_DATA = [
        '_username' => 'test',
        '_password' => 'pass',
    ];

    public function testLoginForm(): void
    {
        $form = $this->factory->create(LoginFormType::class);
        $form->submit(self::FORM_DATA);

        $this->assertTrue($form->isSynchronized());
        self::assertEquals(self::FORM_DATA, $form->getData());
    }

    public function testLoginFormView(): void
    {
        $form = $this->factory->create(LoginFormType::class);
        $fields = $form->createView()->children;

        foreach (array_keys(self::FORM_DATA) as $item) {
            self::assertArrayHasKey($item, $fields);
        }
    }
}
