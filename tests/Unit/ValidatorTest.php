<?php

namespace Tests\Unit;

use Tests\TestCase;
use Validator;

class ValidatorTest extends TestCase
{
    private Validator $validator;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }
    
    public function testRequiredValidationPasses(): void
    {
        $data = ['name' => 'John Doe'];
        $this->validator->setData($data);
        
        $this->validator->required('name');
        
        $this->assertTrue($this->validator->passes());
        $this->assertEmpty($this->validator->getErrors());
    }
    
    public function testRequiredValidationFails(): void
    {
        $data = ['name' => ''];
        $this->validator->setData($data);
        
        $this->validator->required('name');
        
        $this->assertTrue($this->validator->fails());
        $this->assertNotEmpty($this->validator->getErrors());
        $this->assertArrayHasKey('name', $this->validator->getErrors());
    }
    
    public function testEmailValidationPasses(): void
    {
        $data = ['email' => 'test@example.com'];
        $this->validator->setData($data);
        
        $this->validator->email('email');
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testEmailValidationFails(): void
    {
        $data = ['email' => 'invalid-email'];
        $this->validator->setData($data);
        
        $this->validator->email('email');
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('email', $this->validator->getErrors());
    }
    
    public function testMinLengthValidationPasses(): void
    {
        $data = ['password' => 'password123'];
        $this->validator->setData($data);
        
        $this->validator->minLength('password', 8);
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testMinLengthValidationFails(): void
    {
        $data = ['password' => '123'];
        $this->validator->setData($data);
        
        $this->validator->minLength('password', 8);
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('password', $this->validator->getErrors());
    }
    
    public function testMaxLengthValidationPasses(): void
    {
        $data = ['title' => 'Short Title'];
        $this->validator->setData($data);
        
        $this->validator->maxLength('title', 50);
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testMaxLengthValidationFails(): void
    {
        $data = ['title' => str_repeat('a', 100)];
        $this->validator->setData($data);
        
        $this->validator->maxLength('title', 50);
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('title', $this->validator->getErrors());
    }
    
    public function testNumericValidationPasses(): void
    {
        $data = ['age' => '25'];
        $this->validator->setData($data);
        
        $this->validator->numeric('age');
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testNumericValidationFails(): void
    {
        $data = ['age' => 'twenty-five'];
        $this->validator->setData($data);
        
        $this->validator->numeric('age');
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('age', $this->validator->getErrors());
    }
    
    public function testIntegerValidationPasses(): void
    {
        $data = ['count' => '42'];
        $this->validator->setData($data);
        
        $this->validator->integer('count');
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testIntegerValidationFails(): void
    {
        $data = ['count' => '42.5'];
        $this->validator->setData($data);
        
        $this->validator->integer('count');
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('count', $this->validator->getErrors());
    }
    
    public function testMinValidationPasses(): void
    {
        $data = ['age' => '25'];
        $this->validator->setData($data);
        
        $this->validator->min('age', 18);
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testMinValidationFails(): void
    {
        $data = ['age' => '15'];
        $this->validator->setData($data);
        
        $this->validator->min('age', 18);
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('age', $this->validator->getErrors());
    }
    
    public function testMaxValidationPasses(): void
    {
        $data = ['price' => '100'];
        $this->validator->setData($data);
        
        $this->validator->max('price', 200);
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testMaxValidationFails(): void
    {
        $data = ['price' => '300'];
        $this->validator->setData($data);
        
        $this->validator->max('price', 200);
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('price', $this->validator->getErrors());
    }
    
    public function testUrlValidationPasses(): void
    {
        $data = ['website' => 'https://example.com'];
        $this->validator->setData($data);
        
        $this->validator->url('website');
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testUrlValidationFails(): void
    {
        $data = ['website' => 'not-a-url'];
        $this->validator->setData($data);
        
        $this->validator->url('website');
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('website', $this->validator->getErrors());
    }
    
    public function testInValidationPasses(): void
    {
        $data = ['status' => 'active'];
        $this->validator->setData($data);
        
        $this->validator->in('status', ['active', 'inactive', 'pending']);
        
        $this->assertTrue($this->validator->passes());
    }
    
    public function testInValidationFails(): void
    {
        $data = ['status' => 'unknown'];
        $this->validator->setData($data);
        
        $this->validator->in('status', ['active', 'inactive', 'pending']);
        
        $this->assertTrue($this->validator->fails());
        $this->assertArrayHasKey('status', $this->validator->getErrors());
    }
    
    public function testSanitizeRemovesHtmlTags(): void
    {
        $data = ['content' => '<script>alert("xss")</script>Hello World'];
        $this->validator->setData($data);
        
        $this->validator->sanitize('content');
        
        $this->assertEquals('Hello World', $this->validator->get('content'));
    }
    
    public function testEscapeEscapesSpecialCharacters(): void
    {
        $data = ['name' => 'John & Jane <script>'];
        $this->validator->setData($data);
        
        $this->validator->escape('name');
        
        $this->assertEquals('John &amp; Jane &lt;script&gt;', $this->validator->get('name'));
    }
    
    public function testEscapeAllEscapesAllStringFields(): void
    {
        $data = [
            'name' => 'John & Jane',
            'email' => 'test@example.com',
            'age' => 25
        ];
        $this->validator->setData($data);
        
        $this->validator->escapeAll();
        
        $this->assertEquals('John &amp; Jane', $this->validator->get('name'));
        $this->assertEquals('test@example.com', $this->validator->get('email'));
        $this->assertEquals(25, $this->validator->get('age')); // 숫자는 이스케이프되지 않음
    }
    
    public function testGetFirstErrorReturnsFirstError(): void
    {
        $data = ['name' => '', 'email' => 'invalid'];
        $this->validator->setData($data);
        
        $this->validator->required('name');
        $this->validator->email('email');
        
        $firstError = $this->validator->getFirstError();
        $this->assertNotNull($firstError);
        $this->assertStringContainsString('required', $firstError);
    }
    
    public function testGetFirstErrorForSpecificField(): void
    {
        $data = ['name' => '', 'email' => 'invalid'];
        $this->validator->setData($data);
        
        $this->validator->required('name');
        $this->validator->email('email');
        
        $nameError = $this->validator->getFirstError('name');
        $this->assertNotNull($nameError);
        $this->assertStringContainsString('required', $nameError);
        
        $emailError = $this->validator->getFirstError('email');
        $this->assertNotNull($emailError);
        $this->assertStringContainsString('email', $emailError);
    }
    
    public function testResetClearsAllErrors(): void
    {
        $data = ['name' => ''];
        $this->validator->setData($data);
        
        $this->validator->required('name');
        $this->assertTrue($this->validator->fails());
        
        $this->validator->reset();
        $this->assertTrue($this->validator->passes());
        $this->assertEmpty($this->validator->getErrors());
    }
} 