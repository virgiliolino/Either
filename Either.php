<?php 

abstract class Either {
    protected $value;
    public function __construct($value) {
        $this->value = $value;
    }
    public function read() {
        return $this->value;
    }
    
    abstract public function fold($onLeft, $onRight); 
    abstract public function isLeft(): Boolean;
    abstract public function isRight(): Boolean;
}

class Left extends Either {
    
    public function fold($onLeft, $onRight) {
       return $onLeft($this->value); 
    }

    public function isLeft(): Boolean {
        return true;
    }

    public function isRight(): Boolean {
        return false;
    }
}


class Right extends Either {
    
    public function fold($onLeft, $onRight) {
        return $onRight($this->value);
    }

    public function isLeft(): Boolean {
        return false;
    }

    public function isRight(): Boolean {
        return true;
    }

}
