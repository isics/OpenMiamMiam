<?php

/*
 * This file is part of the isicsEntityToIdentifierDataTransformerBundle project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class EntityToIdentifierDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->entityManager = $this->getMock('Doctrine\ORM\EntityManager');
        $this->classMetadata = $this->getMock('Doctrine\ORM\Mapping\ClassMetadata');

        $this->transformer = new EntityToIdentifierDataTransformer($this->entityManager);
    }

    /**
     * @inheritddoc
     */
    protected function tearDown()
    {
        $this->entityManager = null;

        $this->transformer = null;
    }

    /**
     * @dataProvider transformTestDataProvider
     */
    public function testTransform($pks, $expected)
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->classMetadata);

        $this->classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($entity)
            ->will($pks);

        $this->assertEquals($this->transformer->transform(new \stdClass()), $expected);
    }

    public function transformTestDataProvider()
    {
        return array(
            array(
                array('id' => 1),
                '1',
            ),
            array(
                array('id1' => 1, 'id2' => 'ABC'),
                '1-ABC',
            ),
            array(
                array('id1' => 1, 'id2' => 'ABC', 'id3' => 3),
                '1-ABC-3',
            ),
        );
    }
}