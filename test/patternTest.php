<?php
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/../routes/pattern_check.php";

class patternTest extends TestCase {
    // Test Staff no
    public static function staff_no(): array {
        return [
            // invalid sample
            'overlen' => ["CS-9999999999", false],
            'too short len' => ["CS-9", false],
            'other symbol' => ["CS-9@@ ", false],
            'emptyset' => [" ", false],
        ];
    }
    #[DataProvider('staff_no')]
        public function test_staff_no(string $input, bool $expected): void {
            $result = check_phone($input); 
            $this->assertSame($expected, $result);
        }

    public static function phones(): array {
            return [
                // length
                'lower bound' => [str_repeat(1, 8), true],
                'upper bound' => [str_repeat(1, 30), true],
                'overlen' => [str_repeat(1, 31), false],
                'emptyset' => [" ", false],
                // 合法字元
                'have ()' => ["(886) 123 4567", true],
                'have +-' => ["+(886) 123-4567", true],
                // 不合法字元
                'have @' => ["@+(886) 123-4567", false],
                'haveEng' => ["@+(886) 123-ABCD", false],
            ];
        }

        #[DataProvider('phones')]
        public function test_phone(string $input, bool $expected): void {
            $result = check_phone($input); 
            $this->assertSame($expected, $result);
        }
}
?>