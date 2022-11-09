<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\MicroPost\Locales;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;

class RegistrationFormTypeTest extends TypeTestCase
{
    protected const FIELDS_KEY = [
        'login',
        'nick',
        'email',
        'emoji',
        'password',
        'locale',
    ];

    public function testRegistrationFormTypeEmpty(): void
    {
        $user = new User();
        $form = $this->factory->create(RegistrationFormType::class, $user);
        $form->submit([]);
        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }

    public function testRegistrationFormTypeFields(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $fields = $form->createView()->children;
        foreach (self::FIELDS_KEY as $item) {
            self::assertArrayHasKey($item, $fields);
        }
    }

    protected function getExtensions(): array
    {
        $locales = new Locales('ru|en');
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setLocale($locales->getDefaultLocale());
        $requestStack->push($request);

        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension([new RegistrationFormType($locales, $requestStack)], []),
        ];
    }
}
