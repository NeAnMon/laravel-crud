<?php

namespace Bgaze\Crud\Themes\Classic\Builders;

use Bgaze\Crud\Core\Builder;
use Bgaze\Crud\Core\Field;

/**
 * The Show view builder.
 *
 * @author bgaze <benjamin@bgaze.fr>
 */
class ShowView extends Builder {

    /**
     * The file that the builder generates.
     * 
     * @return string The absolute path of the file
     */
    public function file() {
        return resource_path('views/' . $this->crud->getPluralsKebabSlash() . "/show.blade.php");
    }

    /**
     * Build the file.
     */
    public function build() {
        $stub = $this->stub('views.show');
        $this->replace($stub, '#CONTENT', $this->content());
        $this->generateFile($this->file(), $stub);
    }

    /**
     * Build the class content.
     * 
     * @return string
     */
    protected function content() {
        $content = $this->crud->content(false);

        if ($content->isEmpty()) {
            return '    <!-- TODO -->';
        }

        $stub = $this->stub('partials.show-group');

        return $content
                        ->map(function(Field $field) use($stub) {
                            if (in_array($field->name(), ['rememberToken', 'softDeletes', 'softDeletesTz'])) {
                                return null;
                            }

                            if (in_array($field->name(), ['timestamps', 'timestampsTz'])) {
                                return $this->showGroup($stub, 'Created at', 'created_at') . "\n" . $this->showGroup($stub, 'Updated at', 'updated_at');
                            }

                            return $this->showGroup($stub, $field->label(), $field->name());
                        })
                        ->filter()
                        ->implode("\n");
    }

    /**
     * Compile content to request show view group.
     * 
     * @param string $stub
     * @param string $label
     * @param string $name
     * @return string
     */
    protected function showGroup($stub, $label, $name) {
        $this->replace($stub, 'FieldLabel', $label)->replace($stub, 'FieldName', $name);
        return $stub;
    }

}
