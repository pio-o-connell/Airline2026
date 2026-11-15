const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests',
    fullyParallel: false,
    reporter: [['list'], ['html']],
    timeout: 30000,

    use: {
        baseURL: process.env.PW_BASE_URL || 'http://127.0.0.1:8000',
        ...devices['Desktop Chrome'],
        browserName: 'chromium',
        trace: 'on-first-retry'
    },

    webServer: {
        command: 'C:\\php-8.4.26\\php.exe -S 127.0.0.1:8000 controller.php',
        url: process.env.PW_BASE_URL || 'http://127.0.0.1:8000',
        reuseExistingServer: !process.env.CI,
        timeout: 30000
    }
});
