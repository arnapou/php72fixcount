<?php

namespace FixCount\Test\Namespace1 {

    class MultipleNamespace
    {
        public function test1()
        {
            echo count([1, 2, 3]);
            echo \count([1, 2, 3]);
        }

        public function test2()
        {
            echo count([1, 2, 3]);
            echo \count([1, 2, 3]);
        }

        public function test3()
        {
            echo \count([1, 2, 3]);
            echo \another\count([1, 2, 3]);
        }
    }
}

namespace FixCount\Test\Namespace2 {

    class MultipleNamespace
    {
        public function test1()
        {
            echo count([1, 2, 3]);
            echo \count([1, 2, 3]);
        }

        public function test2()
        {
            echo count([1, 2, 3]);
            echo \count([1, 2, 3]);
        }

        public function test3()
        {
            echo \count([1, 2, 3]);
            echo \another\count([1, 2, 3]);
        }
    }
}
