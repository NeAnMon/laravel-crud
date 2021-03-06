<?php

namespace Bgaze\Crud\Core;

use Bgaze\Crud\Core\Crud;
use Illuminate\Filesystem\Filesystem;

/**
 * Builds a file using a CRUD instance
 *
 * @author bgaze <benjamin@bgaze.fr>
 */
abstract class Builder {

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The CRUD instance.
     * 
     * @var \Bgaze\Crud\Core\Crud 
     */
    protected $crud;

    /**
     * The class constructor
     * 
     * @param  \Illuminate\Filesystem\Filesystem  $files The filesystem instance
     * @param Crud $crud
     */
    function __construct(Filesystem $files, Crud $crud) {
        $this->files = $files;
        $this->crud = $crud;
    }

    /**
     * The file that the builder generates.
     * 
     * @return string The absolute path of the file
     */
    abstract public function file();

    /**
     * Build the file.
     * 
     * @return string The relative path of the generated file
     */
    abstract public function build();

    /**
     * Remove base_path() from a path string.
     * 
     * @param string $path  The path of the file
     * @return string       The relative path of the file
     */
    protected function relativePath($path) {
        return str_replace(base_path() . '/', '', $path);
    }

    /**
     * Prepare value for PHP generation depending on it's type.
     * 
     * @param mixed $value
     * @return string
     */
    protected function compileValueForPhp($value) {
        if (is_array($value)) {
            return $this->compileArrayForPhp($value);
        }

        if ($value === true || $value === 'true') {
            return 'true';
        }

        if ($value === false || $value === 'false') {
            return 'false';
        }

        if ($value === null || $value === 'null') {
            return 'null';
        }

        if (!is_numeric($value)) {
            return "'" . addslashes($value) . "'";
        }

        return $value;
    }

    /**
     * Prepare array for PHP generation depending on it's type.
     * 
     * @param mixed $value
     * @return string
     */
    protected function compileArrayForPhp($array, $assoc = false) {
        $entries = collect($array)->map(function($value, $key) use($assoc) {
            if ($assoc) {
                return $this->compileValueForPhp($key) . ' => ' . $this->compileValueForPhp($value);
            }

            return $this->compileValueForPhp($value);
        });

        return '[' . $entries->implode(', ') . ']';
    }

    /**
     * Replace a variable name in a stub string.
     * 
     * @param string $stub          The stub content string
     * @param string $name          The variable name
     * @param string|false $value   The value to use. If false, the '$this->get{$name}()' method is called.
     * @return $this
     */
    protected function replace(&$stub, $name, $value = false) {
        if ($value === false) {
            $value = $this->crud->{'get' . $name}();
        }

        $stub = str_replace($name, $value, $stub);

        return $this;
    }

    /**
     * Get the content of a stub file and populate it with CRUD variables.
     * 
     * @param string $name  The name of the stub
     * @return string       The content of stub file
     */
    public function stub($name) {
        $stubs = $this->crud::stubs();

        // Check that stub exists.
        if (!isset($stubs[$name])) {
            throw new \Exception("Undefined stub '{$stubs[$name]}'.");
        }

        // Get stub content.
        $stub = $this->files->get($stubs[$name]);

        // Replace common variables.
        foreach ($this->crud->variables() as $var) {
            $this->replace($stub, $var);
        }

        return $stub;
    }

    /**
     * Generate a file using a stub file.
     * 
     * @param string $path      The path of the file relative to base_path()
     * @param string $content   The content of the file
     * @return string           The relative path of the file
     */
    protected function generateFile($path, $content) {
        // Prepare file's pathes.
        $relativePath = $this->relativePath($path);
        $absolutePath = base_path($relativePath);

        // Ensure the file doesn't already exists.
        if ($this->files->exists($absolutePath)) {
            throw new \Exception("A '{$relativePath}' file already exists.");
        }

        // Create output dir if necessary.
        if (!$this->files->isDirectory(dirname($absolutePath))) {
            $this->files->makeDirectory(dirname($absolutePath), 0777, true, true);
        }

        // Create file.
        $this->files->put($absolutePath, $content);

        // Return file path.
        return $relativePath;
    }

    /**
     * Generate a file using a stub file then fix it using PHP-CS-Fixer.
     * 
     * @param string $path      The path of the file relative to base_path()
     * @param string $content   The content of the file
     * @return string           The relative path of the file
     */
    protected function generatePhpFile($path, $content) {
        // Generate file.
        $relativePath = $this->generateFile($path, $content);

        // Fix it with PhpCsFixer.
        php_cs_fixer($relativePath, ['--quiet' => true]);

        // Return file path.
        return $relativePath;
    }

    /**
     * Check that the file to generate doesn't exists.
     * 
     * @return false|string     The error message if file exists, false otherwise
     */
    public function fileExists() {
        if ($this->files->exists($this->file())) {
            return $this->relativePath($this->file());
        }

        return false;
    }

}
