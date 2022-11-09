<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Form\LoginFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class LoginFormTypeTest extends TypeTestCase
{
    public function testLoginForm(): void
    {
        $formData = [
            '_username' => 'test',
            '_password' => 'pass',
        ];

        $form = $this->factory->create(LoginFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        self::assertEquals($formData, $form->getData());
    }
}
