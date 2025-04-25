<?php

declare(strict_types = 1);

namespace Modules\Skeleton\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ModuleBuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module-build {--name= : The name of the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module by copying the Skeleton module structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating a new module');

        if ($this->option('name')) {
            $moduleName = $this->option('name');
        } else {
            $moduleName = $this->ask('What is the name of the module?');
        }

        if (empty($moduleName)) {
            $this->error('Module name is required!');

            return 1;
        }

        // Standardize the name (first letter uppercase)
        $moduleName      = ucfirst($moduleName);
        $moduleNameLower = Str::lower($moduleName);

        // Check if module already exists
        $modulePath = base_path('Modules/' . $moduleName);

        if (File::exists($modulePath)) {
            $this->error("Module {$moduleName} already exists!");

            return 1;
        }

        // Skeleton module path
        $skeletonPath = base_path('Modules/Skeleton');

        $this->info("Copying Skeleton module to {$moduleName}...");

        // Copy files from Skeleton to new module
        File::copyDirectory($skeletonPath, $modulePath);

        // Remove .git directory from new module if it exists
        if (File::exists($modulePath . '/.git')) {
            File::deleteDirectory($modulePath . '/.git');
            $this->info("Removing .git directory from new module...");
        }

        // Remove /vendor directory from new module if it exists
        if (File::exists($modulePath . '/vendor')) {
            File::deleteDirectory($modulePath . '/vendor');
            $this->info("Removing vendor directory from new module...");
        }

        // Now we need to rename all files containing "Skeleton" in the name
        $this->info("Renaming files...");
        $this->renameFiles($modulePath, 'Skeleton', $moduleName);

        // Replace all occurrences of "Skeleton" and "skeleton" in files
        $this->info("Replacing references in files...");
        $this->replaceInFiles($modulePath, 'Skeleton', $moduleName, 'skeleton', $moduleNameLower);

        // Update specific files
        $this->info("Updating specific configurations...");
        $this->updateComposerJson($modulePath, $moduleNameLower);
        $this->updateModuleJson($modulePath, $moduleName, $moduleNameLower);
        $this->updateServiceProvider($modulePath, $moduleName, $moduleNameLower);

        // Remove this command from the new module
        $this->info("Removing ModuleBuildCommand from the new module...");
        $commandPath = $modulePath . '/app/Console/Commands/ModuleBuildCommand.php';

        if (File::exists($commandPath)) {
            File::delete($commandPath);
        }

        // Remove Console directory if empty
        $consoleDir  = $modulePath . '/app/Console';
        $commandsDir = $modulePath . '/app/Console/Commands';

        if (File::exists($commandsDir) && count(File::files($commandsDir)) === 0 && count(File::directories($commandsDir)) === 0) {
            File::deleteDirectory($commandsDir);
        }

        if (File::exists($consoleDir) && count(File::files($consoleDir)) === 0 && count(File::directories($consoleDir)) === 0) {
            File::deleteDirectory($consoleDir);
        }

        // Run composer dump-autoload
        $this->info("Running composer dump-autoload...");
        $process = new Process(['composer', 'dump-autoload']);
        $process->run();

        if ($process->isSuccessful()) {
            $this->info($process->getOutput());
        } else {
            $this->error("Failed to run composer dump-autoload");
            $this->error($process->getErrorOutput());
        }

        $this->newLine();
        $this->info("Module {$moduleName} created successfully!");
        $this->info("Path: {$modulePath}");
        $this->newLine();

        // Enable the new module
        $this->info("Enabling the new module...");

        $modulesFile = base_path('modules_statuses.json');

        if (File::exists($modulesFile)) {
            $modules              = json_decode(File::get($modulesFile), true);
            $modules[$moduleName] = true;
            File::put($modulesFile, json_encode($modules, JSON_PRETTY_PRINT));
            $this->info("Module {$moduleName} enabled successfully!");
        } else {
            $this->warn("modules_statuses.json file not found!");
        }

        return self::SUCCESS;
    }

    /**
     * Update the composer.json file
     */
    private function updateComposerJson($modulePath, $moduleNameLower)
    {
        $filePath = $modulePath . '/composer.json';

        if (! File::exists($filePath)) {
            $this->warn("composer.json file not found!");

            return;
        }

        $this->line("Updating composer.json...");

        $content = json_decode(File::get($filePath), true);

        // Update name
        $content['name'] = "e2tmk/{$moduleNameLower}-module";

        // Update namespaces in autoload and autoload-dev
        $content['autoload']['psr-4'] = $this->updateNamespaces($content['autoload']['psr-4'], 'Skeleton', ucfirst($moduleNameLower));

        if (isset($content['autoload-dev']['psr-4'])) {
            $content['autoload-dev']['psr-4'] = $this->updateNamespaces($content['autoload-dev']['psr-4'], 'Skeleton', ucfirst($moduleNameLower));
        }

        // Save the file
        File::put($filePath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->line("composer.json updated.");
    }

    /**
     * Update namespace keys in an associative array
     */
    private function updateNamespaces($namespaces, $oldName, $newName)
    {
        $updatedNamespaces = [];

        foreach ($namespaces as $namespace => $path) {
            $newNamespace                     = str_replace("\\{$oldName}\\", "\\{$newName}\\", $namespace);
            $updatedNamespaces[$newNamespace] = $path;
        }

        return $updatedNamespaces;
    }

    /**
     * Update the module.json file
     */
    private function updateModuleJson($modulePath, $moduleName, $moduleNameLower)
    {
        $filePath = $modulePath . '/module.json';

        if (! File::exists($filePath)) {
            $this->warn("module.json file not found!");

            return;
        }

        $this->line("Updating module.json...");

        $content = json_decode(File::get($filePath), true);

        // Update name and alias
        $content['name']  = $moduleName;
        $content['alias'] = $moduleNameLower;

        // Update providers
        if (isset($content['providers'])) {
            $updatedProviders = [];

            foreach ($content['providers'] as $provider) {
                $updatedProviders[] = str_replace('Skeleton', $moduleName, $provider);
            }
            $content['providers'] = $updatedProviders;
        }

        // Update Filament plugins if they exist
        if (isset($content['filament_plugins'])) {
            $updatedPlugins = [];

            foreach ($content['filament_plugins'] as $plugin) {
                $updatedPlugins[] = str_replace('Skeleton', $moduleName, $plugin);
            }
            $content['filament_plugins'] = $updatedPlugins;
        }

        // Save the file
        File::put($filePath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->line("module.json updated.");
    }

    /**
     * Update the ServiceProvider file
     */
    private function updateServiceProvider($modulePath, $moduleName, $moduleNameLower)
    {
        $providerPath = $modulePath . '/app/Providers/' . $moduleName . 'ServiceProvider.php';

        if (! File::exists($providerPath)) {
            $this->warn("ServiceProvider not found at {$providerPath}!");

            return;
        }

        $this->line("Updating ServiceProvider...");

        $content = File::get($providerPath);

        // Update namespace
        $content = str_replace(
            'namespace Modules\\Skeleton\\Providers;',
            'namespace Modules\\' . $moduleName . '\\Providers;',
            $content
        );

        // Update the name and nameLower properties
        $content = preg_replace(
            '/protected string \$name = \'Skeleton\';/',
            "protected string \$name = '{$moduleName}';",
            $content
        );

        $content = preg_replace(
            '/protected string \$nameLower = \'skeleton\';/',
            "protected string \$nameLower = '{$moduleNameLower}';",
            $content
        );

        // Update the class name
        $content = preg_replace(
            '/class SkeletonServiceProvider extends ServiceProvider/',
            "class {$moduleName}ServiceProvider extends ServiceProvider",
            $content
        );

        // Save the file
        File::put($providerPath, $content);

        $this->line("ServiceProvider updated.");
    }

    /**
     * Rename files containing the pattern in their names.
     */
    private function renameFiles($directory, $search, $replace)
    {
        $searchLower  = strtolower($search);
        $replaceLower = strtolower($replace);

        // Process directories first (bottom-up to avoid issues with renaming)
        $directories = array_reverse(File::directories($directory));

        foreach ($directories as $dir) {
            $this->renameFiles($dir, $search, $replace);

            // Rename the directory itself if needed
            $dirName = basename($dir);

            if (Str::contains($dirName, $search) || Str::contains($dirName, $searchLower)) {
                $newDirName = str_replace(
                    [$search, $searchLower],
                    [$replace, $replaceLower],
                    $dirName
                );

                $newDirPath = str_replace($dirName, $newDirName, $dir);

                if (File::exists($dir) && $dirName !== $newDirName) {
                    File::move($dir, $newDirPath);
                    $this->line("Directory renamed: {$dirName} -> {$newDirName}");
                }
            }
        }

        // Now process files
        $files = File::files($directory);

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileName = $file->getFilename();

            // Check if the filename contains the pattern
            if (Str::contains($fileName, $search) || Str::contains($fileName, $searchLower)) {
                $newFileName = str_replace(
                    [$search, $searchLower],
                    [$replace, $replaceLower],
                    $fileName
                );

                $newFilePath = str_replace($fileName, $newFileName, $filePath);

                // Rename the file
                if (File::exists($filePath) && $fileName !== $newFileName) {
                    File::move($filePath, $newFilePath);
                    $this->line("File renamed: {$fileName} -> {$newFileName}");
                }
            }
        }
    }

    /**
     * Replace all occurrences of patterns in files.
     */
    private function replaceInFiles($directory, $search, $replace, $searchLower, $replaceLower)
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $filePath = $file->getPathname();

            // Skip binary files or specific files that will be handled separately
            if ($this->isBinaryFile($filePath) || $this->shouldSkipFile($filePath)) {
                continue;
            }

            // Read file content
            $content = File::get($filePath);

            // Replace occurrences
            $newContent = str_replace(
                [$search, $searchLower],
                [$replace, $replaceLower],
                $content
            );

            // If changes were made, save the file
            if ($content !== $newContent) {
                File::put($filePath, $newContent);
                $this->line("Content updated: " . $file->getRelativePathname());
            }
        }
    }

    /**
     * Check if a file should be skipped in general replacement
     */
    private function shouldSkipFile($filePath)
    {
        // Files that will be processed separately
        $skipFiles = [
            'composer.json',
            'module.json',
        ];

        $fileName = basename($filePath);

        if (in_array($fileName, $skipFiles)) {
            return true;
        }

        // Check if it's a ServiceProvider
        if (Str::endsWith($fileName, 'ServiceProvider.php')) {
            return true;
        }

        return false;
    }

    /**
     * Check if a file is binary.
     */
    private function isBinaryFile($filePath)
    {
        // Common binary file extensions
        $binaryExtensions = ['png', 'jpg', 'jpeg', 'gif', 'ico', 'zip', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'bin', 'exe', 'dll', 'so', 'dat', 'cache'];
        $extension        = pathinfo($filePath, PATHINFO_EXTENSION);

        if (in_array(strtolower($extension), $binaryExtensions)) {
            return true;
        }

        // Try to open the file - if it contains binary characters, return true
        $f = fopen($filePath, 'r');

        if (! $f) {
            return true; // If can't open, consider it binary for safety
        }

        $blocksize = 512;
        $binary    = false;

        // Read the beginning of the file to detect binary content
        $data = fread($f, $blocksize);
        fclose($f);

        // If there's a null byte or other specific control characters, it's considered binary
        if (strpos($data, "\0") !== false) {
            return true;
        }

        return false;
    }
}
