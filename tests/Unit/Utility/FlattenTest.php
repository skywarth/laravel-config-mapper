<?php

namespace Skywarth\LaravelConfigMapper\Tests\Unit\Utility;

use Skywarth\LaravelConfigMapper\Utility;

class FlattenTest extends \Skywarth\LaravelConfigMapper\Tests\TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_empty_array()
    {
        $sample=[];
        $flat=Utility::flatten($sample);
        $this->assertTrue($sample===$flat);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_one_dimensional_array()
    {
        $sample=['a','b','c','d'];
        $expected=['a','b','c','d'];
        $flat=Utility::flatten($sample);
        $this->assertTrue($flat===$expected);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_two_dimensional_regular_array_no_value()
    {
        $sample=[
            'a'=>['a1','a2','a3'],
            'b'=>['b1','b2','b3'],
        ];
        $expected=[
            'a.0'=>'a1',
            'a.1'=>'a2',
            'a.2'=>'a3',
            'b.0'=>'b1',
            'b.1'=>'b2',
            'b.2'=>'b3',
        ];

        $flat=Utility::flatten($sample);
        $this->assertTrue($flat===$expected);
    }



    //USE THE GROUP NOTATION
    /**
     * @group some-group
     */
    public function test_two_dimensional_regular_array()
    {
        $sample=[
            'a'=>[
                'key1'=>'a1',
                'key2'=>'a2',
                'key3'=>'a3'
            ],
            'b'=>[
                'key1'=>'b1',
                'key2'=>'b2',
                'key3'=>'b3'
            ]
        ];
        $expected=[
            'a.key1'=>'a1',
            'a.key2'=>'a2',
            'a.key3'=>'a3',
            'b.key1'=>'b1',
            'b.key2'=>'b2',
            'b.key3'=>'b3',
        ];

        $flat=Utility::flatten($sample);
        $this->assertTrue($flat===$expected);
    }

    //USE THE GROUP NOTATION
    /**
     * @group some-group
     */
    public function test_two_dimensional_jagged_array()
    {
        $sample=[
            'a'=>[
                'key1'=>'a1',
                'key2'=>[
                    'foo'=>'baz'
                ],
                'key3'=>'a3'
            ],
            'b'=>[
                'key1'=>[
                    'bar'=>[
                        'fob'=>'fobz'
                    ]
                ],
                'key2'=>'b2',
                'key3'=>[
                    'foo'=>'baz'
                ]
            ]
        ];
        $expected=[
            'a.key1'=>'a1',
            'a.key2.foo'=>'baz',
            'a.key3'=>'a3',
            'b.key1.bar.fob'=>'fobz',
            'b.key2'=>'b2',
            'b.key3.foo'=>'baz',
        ];

        $flat=Utility::flatten($sample);
        $this->assertTrue($flat===$expected);
    }


    //USE THE GROUP NOTATION
    /**
     * @group some-group
     */
    public function test_two_dimensional_jagged_array_null_value()
    {
        $sample=[
            'a'=>[
                'key1'=>'a1',
                'key2'=>[
                    'foo'=>null
                ],
                'key3'=>'a3'
            ],
            'b'=>[
                'key1'=>[
                    'bar'=>[
                        'fob'=>'fobz'
                    ]
                ],
                'key2'=>null,
                'key3'=>[
                    'foo'=>'baz'
                ]
            ]
        ];
        $expected=[
            'a.key1'=>'a1',
            'a.key2.foo'=>null,
            'a.key3'=>'a3',
            'b.key1.bar.fob'=>'fobz',
            'b.key2'=>null,
            'b.key3.foo'=>'baz',
        ];

        $flat=Utility::flatten($sample);
        $this->assertTrue($flat===$expected);
    }
}