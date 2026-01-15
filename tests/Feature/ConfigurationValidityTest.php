<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConfigurationValidityTest extends TestCase
{
    /**
     * @test
     * Feature: infodot-modernization, Property 26: Configuration Validity
     * 
     * For any configuration file, the structure and values should be valid 
     * for Laravel 11 and not cause runtime errors.
     * 
     * Validates: Requirements MR-4
     */
    public function property_configuration_validity()
    {
        // Test that all config files can be loaded without errors
        $configFiles = [
            'app',
            'auth',
            'broadcasting',
            'cache',
            'database',
            'filesystems',
            'fortify',
            'jetstream',
            'logging',
            'mail',
            'media-library',
            'queue',
            'sanctum',
            'scout',
            'services',
            'session',
        ];

        foreach ($configFiles as $configFile) {
            $config = config($configFile);
            $this->assertIsArray($config, "Config file '{$configFile}' should return an array");
            $this->assertNotEmpty($config, "Config file '{$configFile}' should not be empty");
        }

        // Test critical configuration values
        $this->assertNotEmpty(config('app.key'), 'APP_KEY should be set');
        
        // In testing environment, we use SQLite for speed
        // In production, MySQL is used
        if (app()->environment('testing')) {
            $this->assertEquals('sqlite', config('database.default'), 'Test database should be sqlite');
            $this->assertNull(config('scout.driver'), 'Scout should be disabled in testing');
        } else {
            $this->assertEquals('mysql', config('database.default'), 'Production database should be mysql');
            $this->assertEquals('tntsearch', config('scout.driver'), 'Scout driver should be tntsearch');
        }
        
        $this->assertEquals('reverb', config('broadcasting.default'), 'Broadcasting should use reverb');
    }

    /**
     * @test
     * Test database connection configuration
     */
    public function test_database_configuration_is_valid()
    {
        // In testing, we use SQLite for speed
        if (app()->environment('testing')) {
            $this->assertEquals('sqlite', config('database.default'));
            $this->assertStringContainsString('testing.sqlite', config('database.connections.sqlite.database'));
        } else {
            $this->assertEquals('mysql', config('database.default'));
            $this->assertEquals('127.0.0.1', config('database.connections.mysql.host'));
            $this->assertEquals('3306', config('database.connections.mysql.port'));
            $this->assertEquals('infodot_laravel11', config('database.connections.mysql.database'));
        }
    }

    /**
     * @test
     * Test Scout configuration
     */
    public function test_scout_configuration_is_valid()
    {
        // In testing, Scout is disabled for performance
        if (app()->environment('testing')) {
            $this->assertNull(config('scout.driver'));
        } else {
            $this->assertEquals('tntsearch', config('scout.driver'));
            $this->assertFalse(config('scout.queue'));
            $this->assertArrayHasKey('tntsearch', config('scout'));
        }
    }

    /**
     * @test
     * Test broadcasting configuration
     */
    public function test_broadcasting_configuration_is_valid()
    {
        $this->assertEquals('reverb', config('broadcasting.default'));
        $this->assertArrayHasKey('reverb', config('broadcasting.connections'));
    }
}
