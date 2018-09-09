<?php

namespace Bgaze\Crud\Themes\Classic\Builders;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Bgaze\Crud\Core\Crud;
use Bgaze\Crud\Core\Builder;
use Bgaze\Crud\Core\Field;

/**
 * The Migration class builder
 *
 * @author bgaze <benjamin@bgaze.fr>
 */
class Migration extends Builder {

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * As it contains a timestamp, we store the file name when generated.
     * 
     * @var string 
     */
    protected $file;

    /**
     * The class constructor
     * 
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Bgaze\Crud\Core\Crud $crud
     */
    public function __construct(Filesystem $files, Crud $crud) {
        parent::__construct($files, $crud);

        $this->composer = resolve('Illuminate\Support\Composer');
    }

    /**
     * The file that the builder generates.
     * 
     * @return string The absolute path of the file
     */
    public function file() {
        if (empty($this->file)) {
            $file = Str::snake($this->crud->getMigrationClass());
            $prefix = date('Y_m_d_His');
            $this->file = database_path("migrations/{$prefix}_{$file}.php");
        }

        return $this->file;
    }

    /**
     * Check that the file to generate doesn't exists.
     * 
     * @return false|string The error message if file exists, false otherwise
     */
    public function fileExists() {
        $file = Str::snake($this->crud->getMigrationClass());
        $files = $this->files->glob(database_path("migrations/*_{$file}.php"));

        if (count($files) === 1) {
            return $this->relativePath($files[0]);
        }

        if (count($files) > 1) {
            return "migrations/*_{$file}.php (" . count($files) . ")";
        }

        return false;
    }

    /**
     * Build the file.
     * 
     * @return string The relative path of the generated file
     */
    public function build() {
        // Write migration file.
        $stub = $this->stub('migration');
        $this->replace($stub, '#CONTENT', $this->content());
        $path = $this->generatePhpFile($this->file(), $stub);

        // Update autoload.
        $this->composer->dumpAutoloads();

        // Return relative path.
        return $path;
    }

    /**
     * Build the migration content.
     * 
     * @return string
     */
    protected function content() {
        $content = $this->crud->content()->map(function(Field $field) {
            return $this->migrationGroup($field);
        });

        if ($this->crud->softDeletes()) {
            $content->prepend(config('crud-definitions.softDeletes.' . $this->crud->softDeletes()));
        }

        if ($this->crud->timestamps()) {
            $content->prepend(config('crud-definitions.timestamps.' . $this->crud->timestamps()));
        }

        return $content->implode("\n");
    }

    /**
     * Compile content to migration class body line.
     * 
     * @return string
     */
    protected function migrationGroup(Field $field) {
        $tmp = $field->config('template');

        foreach ($field->input()->getArguments() as $k => $v) {
            $tmp = str_replace("%$k", $this->compileValueForPhp($v), $tmp);
        }

        foreach ($field->input()->getOptions() as $k => $v) {
            if ($v) {
                $tmp .= str_replace('%value', $this->compileValueForPhp($v), config("crud-definitions.modifiers.{$k}"));
            }
        }

        return $tmp . ';';
    }

}