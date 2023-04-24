<?php

namespace App;

class CaseInput
{
    //protected $content;
    /**
     * CaseInput constructor.
     */
    public function __construct()
    {
    }

    public function __get($property)
    {
        if (isset($this->$property)) {
            return $this->$property;
        }
        throw new PHPUnit\Exception('Unkown property access.');
    }

    public function text($name)
    {
        $newinput = '{"type": "text","name": "' . $name . '"}';
        $this->assignToContent($newinput);

        return $this;
    }

    public function assignToContent($content)
    {
        $this->content .= $content . ',';
    }

    /**
     * @param  null  $format
     * @return $this
     */
    public function dateI($name)
    {
        $newinput = '{"type": "date","name": "' . $name . '"}';
        $this->assignToContent($newinput);

        return $this;
    }

    public function multiplechoice($name, $choices)
    {
        $choices = explode(',', $choices);
        $newinput = '{"type": "multiple_choice","name": "' . $name . '","choices": ' . json_encode($choices) . ' }';
        $this->assignToContent($newinput);

        return $this;
    }

    public function onechoice($name, $choices)
    {
        $choices = explode(',', $choices);
        $newinput = '{"type": "one_choice","name": "' . $name . '","choices": ' . json_encode($choices) . ' }';
        $this->assignToContent($newinput);

        return $this;
    }

    public function format()
    {

        $content = json_decode('[' . substr($this->content, 0, -1) . ']');
        $i = 0;
        foreach ($content as $key => $value) {
            $value->id = $i;
            $this->{$key} = $value;
            $i++;
        }
    }
}
