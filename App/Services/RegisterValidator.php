<?php
// app/Services/RegisterValidator.php
namespace App\Services;

class RegisterValidator
{
    protected $data;
    protected $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function passes(): bool
    {
        $this->errors = [];
        $full_name = trim($this->data['full_name'] ?? '');
        $company_name = trim($this->data['company_name'] ?? '');
        $email = trim($this->data['email'] ?? '');
        $password = trim($this->data['password'] ?? '');
        $password2 = trim($this->data['password2'] ?? '');
        $terms = $this->data['terms_of_service'] ?? null;
        $recaptcha = $this->data['g-recaptcha-response'] ?? '';

        if ($full_name === '') {
            $this->errors[] = 'Ad Soyad alanı boş bırakılamaz.';
        } elseif (mb_strlen($full_name) < 3) {
            $this->errors[] = 'Ad Soyad en az 3 karakter olmalıdır.';
        }
        if ($company_name === '') {
            $this->errors[] = 'Firma adı boş bırakılamaz.';
        } elseif (mb_strlen($company_name) < 3) {
            $this->errors[] = 'Firma adı en az 3 karakter olmalıdır.';
        }
        if ($email === '') {
            $this->errors[] = 'Email alanı boş bırakılamaz.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Geçerli bir email adresi giriniz.';
        }
        if ($password === '') {
            $this->errors[] = 'Şifre alanı boş bırakılamaz.';
        } elseif (mb_strlen($password) < 6) {
            $this->errors[] = 'Şifre en az 6 karakter olmalıdır.';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $this->errors[] = 'Şifre en az bir büyük harf, bir küçük harf ve bir rakam içermelidir.';
        }
        if ($password !== $password2) {
            $this->errors[] = 'Şifreler eşleşmiyor.';
        }
        if (!$terms) {
            $this->errors[] = 'Şartlar ve koşulları kabul etmelisiniz.';
        }
        if ($recaptcha === '') {
            $this->errors[] = 'Lütfen reCAPTCHA doğrulamasını yapınız.';
        }
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }
}
