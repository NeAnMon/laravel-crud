<?php

namespace Bgaze\Crud\Theme;

use Illuminate\Support\Str;
use Bgaze\Crud\Theme\Crud;
use Bgaze\Crud\Core\Field as Base;

/**
 * TODO
 *
 * @author bgaze
 */
class Field extends Base {

    /**
     * TODO
     */
    public function getLabel() {
        $label = Str::studly($this->name);

        if (!preg_match_all('/[A-Z][a-z]+|\d+/', $label, $matches) || !isset($matches[0])) {
            return $label;
        }

        return implode(' ', $matches[0]);
    }

    /**
     * Compile field to migration PHP sentence.
     * 
     * @return string
     */
    public function toMigration() {
        $tmp = $this->config('template');

        foreach ($this->input->getArguments() as $k => $v) {
            $tmp = str_replace("%$k", $this->crud->compileValueForPhp($v), $tmp);
        }

        foreach ($this->input->getOptions() as $k => $v) {
            if ($v) {
                $tmp .= str_replace('%value', $this->crud->compileValueForPhp($v), config("crud-definitions.modifiers.{$k}"));
            }
        }

        return $tmp . ';';
    }

    /**
     * TODO
     * 
     * @return string
     */
    public function toFactory() {
        if ($this->isIndex()) {
            return null;
        }

        $template = $this->config('factory');

        if (!$template) {
            $template = $this->getDefaultFactoryTemplate();
        }

        return "'{$this->name}' => {$template},";
    }

    /**
     * TODO
     * 
     * @return string
     */
    protected function getDefaultFactoryTemplate() {
        switch ($this->config('type')) {
            case 'boolean':
                return '(mt_rand(0, 1) === 1)';
            case 'integer':
                return 'mt_rand(0, 1000)';
            case 'float':
                return "(mt_rand() / mt_getrandmax()) * " . str_repeat('9', $this->input->getArgument('total'));
            case 'date':
                return "Carbon::createFromTimeStamp(\$faker->dateTimeBetween('-30 days', '+30 days')->getTimestamp())";
            case 'array':
                return 'array_random(' . $this->crud->compileValueForPhp($this->input->getArgument('allowed')) . ')';
            default:
                return "\$faker->sentence()";
        }
    }

    /**
     * TODO
     * 
     * @return string
     */
    public function toRequest() {
        if ($this->isIndex()) {
            return null;
        }

        $rules = [];

        if ($this->options->contains('nullable')) {
            $rules[] = $this->input->getOption('nullable') ? 'nullable' : 'required';
        } elseif (preg_match('/^nullable/', $this->type)) {
            $rules[] = 'nullable';
        } else {
            $rules[] = 'required';
        }

        $rules[] = $this->getTypeRules($this->config('type'));

        if ($this->options->contains('unique') && $this->input->getOption('unique')) {
            $rules[] = 'unique:' . $this->crud->getTableName() . ',' . $this->name;
        }

        return sprintf("'%s' => '%s',", $this->name, implode('|', array_filter($rules)));
    }

    /**
     * TODO
     * 
     * @param type $type
     * @return string|null
     */
    protected function getTypeRules($type) {
        switch ($type) {
            case 'boolean':
                return 'boolean';
            case 'integer':
                return 'integer';
            case 'float':
                return 'numeric';
            case 'date':
                return 'date';
            case 'array':
                return 'in:' . implode(',', $this->input->getArgument('allowed'));
            default:
                return null;
        }
    }

    /**
     * TODO
     * 
     * @return string
     */
    public function toTableHead() {
        if ($this->isIndex()) {
            return null;
        }

        return $this->crud->populateStub('views.partial.table-head', function(Crud $crud, $stub) {
                    $crud->replace($stub, 'FieldLabel', $this->label);
                });
    }

    /**
     * TODO
     * 
     * @return string
     */
    public function toTableBody() {
        if ($this->isIndex()) {
            return null;
        }

        return $this->crud->populateStub('views.partial.table-body', function(Crud $crud, $stub) {
                    $crud
                            ->replace($stub, 'FieldLabel', $this->label)
                            ->replace($stub, 'FieldName', $this->name)
                    ;
                });
    }

    /**
     * TODO
     * 
     * @return string
     */
    public function toForm($create) {
        if ($this->isIndex()) {
            return null;
        }

        return $this->crud->populateStub('views.partial.form-group', function(Crud $crud, $stub) {
                    $crud
                            ->replace($stub, 'FieldLabel', $this->label)
                            ->replace($stub, '#FIELD', $this->getDefaultFormTemplate())
                            ->replace($stub, 'FieldName', $this->name)
                    ;
                });
    }

    /**
     * TODO
     * 
     * @return string
     */
    protected function getDefaultFormTemplate() {
        switch ($this->config('type')) {
            case 'boolean':
                return "Form::checkbox('FieldName', '1')";
            case 'array':
                return "Form::select('FieldName', " . $this->crud->compileValueForPhp($this->input->getArgument('allowed')) . ")";
            default:
                return "Form::text('FieldName')";
        }
    }

    /**
     * TODO
     * 
     * @return string
     */
    public function toShow() {
        if ($this->isIndex()) {
            return null;
        }

        return $this->crud->populateStub('views.partial.show-group', function(Crud $crud, $stub) {
                    $crud
                            ->replace($stub, 'ModelCamel')
                            ->replace($stub, 'FieldLabel', $this->label)
                            ->replace($stub, 'FieldName', $this->name)
                    ;
                });
    }

}
