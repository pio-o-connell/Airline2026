import { defineConfig, devices } from '@playwright/test';

// Select the PHP executable for the current operating system.
// Windows uses the locally installed PHP 8.4 executable.
// GitHub Actions (Ubuntu) uses PHP installed by setup-php.
const phpCommand = process.platform === 'win32'
    ? '"C:\\php-8.4.26\\php.exe"'
    : 'php';

const baseURL = process.env.PW_BASE_URL || 'http://127.0.0.1:8000';

export default defineConfig({
    testDir: './tests',
    fullyParallel: false,
    reporter: [
        ['list'], 
        ['github'],
        ['html']
    ],   
    timeout: 30000,

    use: {
        baseURL,
        ...devices['Desktop Chrome'],
        browserName: 'chromium',
        trace: 'on-first-retry'
    },

    webServer: {
        // Start the PHP application using the platform-specific executable.
        command: `${phpCommand} -S 127.0.0.1:8000 controller.php`,
        url: baseURL,
        reuseExistingServer: !process.env.CI,
        timeout: 30000
    }
});
