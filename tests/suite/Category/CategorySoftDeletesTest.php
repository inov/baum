<?php

class CategorySoftDeletesTest extends CategoryTestCase {

  public function testReload() {
    $node = $this->categories('Child 3', 'SoftCategory');

    $node->delete();

    $this->assertTrue($node->trashed());
    $this->assertFalse($node->exists);

    $node->reload();

    $this->assertTrue($node->trashed());
    $this->assertTrue($node->exists);
  }

  public function testDeleteMaintainsTreeValid() {
    $this->assertTrue(SoftCategory::isValid());

    $child3 = $this->categories('Child 3', 'SoftCategory');
    $child3->delete();

    $this->assertTrue($child3->trashed());
    $this->assertTrue(SoftCategory::isValid());
  }

  public function testDeleteMaintainsTreeValidWithSubtrees() {
    $this->assertTrue(SoftCategory::isValid());

    $child2 = $this->categories('Child 2', 'SoftCategory');
    $child2->delete();
    $this->assertTrue(SoftCategory::isValid());

    $expected = array(
      $this->categories('Child 1', 'SoftCategory'),
      $this->categories('Child 3', 'SoftCategory')
    );
    $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());
  }

  public function testDeleteShiftsIndexes() {
    $this->assertTrue(SoftCategory::isValid());

    $this->categories('Child 1', 'SoftCategory')->delete();

    $this->assertTrue(SoftCategory::isValid());

    $expected = array(
      $this->categories('Child 2'   , 'SoftCategory'),
      $this->categories('Child 2.1' , 'SoftCategory'),
      $this->categories('Child 3'   , 'SoftCategory')
    );
    $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

    $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
    $this->assertEquals(8, $this->categories('Root 1', 'SoftCategory')->getRight());

    $this->assertEquals(2, $this->categories('Child 2', 'SoftCategory')->getLeft());
    $this->assertEquals(5, $this->categories('Child 2', 'SoftCategory')->getRight());

    $this->assertEquals(3, $this->categories('Child 2.1', 'SoftCategory')->getLeft());
    $this->assertEquals(4, $this->categories('Child 2.1', 'SoftCategory')->getRight());

    $this->assertEquals(6, $this->categories('Child 3', 'SoftCategory')->getLeft());
    $this->assertEquals(7, $this->categories('Child 3', 'SoftCategory')->getRight());
  }

  public function testDeleteShiftsIndexesSubtree() {
    $this->assertTrue(SoftCategory::isValid());

    $this->categories('Child 2', 'SoftCategory')->delete();

    $this->assertTrue(SoftCategory::isValid());

    $expected = array(
      $this->categories('Child 1', 'SoftCategory'),
      $this->categories('Child 3', 'SoftCategory')
    );
    $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

    $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
    $this->assertEquals(6, $this->categories('Root 1', 'SoftCategory')->getRight());

    $this->assertEquals(2, $this->categories('Child 1', 'SoftCategory')->getLeft());
    $this->assertEquals(3, $this->categories('Child 1', 'SoftCategory')->getRight());

    $this->assertEquals(4, $this->categories('Child 3', 'SoftCategory')->getLeft());
    $this->assertEquals(5, $this->categories('Child 3', 'SoftCategory')->getRight());
  }

  public function testDeleteShiftsIndexesFullSubtree() {
    $this->assertTrue(SoftCategory::isValid());

    $this->categories('Root 1', 'SoftCategory')->delete();
    $this->assertTrue(SoftCategory::isValid());

    $this->assertEmpty($this->categories('Root 2', 'SoftCategory')->getSiblings()->all());
    $this->assertEquals(1, $this->categories('Root 2', 'SoftCategory')->getLeft());
    $this->assertEquals(2, $this->categories('Root 2', 'SoftCategory')->getRight());
  }

  public function testRestoreMaintainsTreeValid() {
    $this->assertTrue(SoftCategory::isValid());

    $child3 = $this->categories('Child 3', 'SoftCategory');
    $child3->delete();

    $this->assertTrue($child3->trashed());
    $this->assertTrue(SoftCategory::isValid());

    $child3->reload();
    $child3->restore();

    $this->assertFalse($child3->trashed());
    $this->assertTrue(SoftCategory::isValid());
  }

  public function testRestoreMaintainsTreeValidWithSubtrees() {
    $this->assertTrue(SoftCategory::isValid());

    $child2 = $this->categories('Child 2', 'SoftCategory');
    $child2->delete();
    $this->assertTrue(SoftCategory::isValid());

    $child2->reload();
    $child2->restore();

    $this->assertTrue(SoftCategory::isValid());

    $expected = array(
      $this->categories('Child 1'   , 'SoftCategory'),
      $this->categories('Child 2'   , 'SoftCategory'),
      $this->categories('Child 2.1' , 'SoftCategory'),
      $this->categories('Child 3'   , 'SoftCategory')
    );
    $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());
  }

  public function testRestoreUnshiftsIndexes() {
    $this->assertTrue(SoftCategory::isValid());

    $this->categories('Child 1', 'SoftCategory')->delete();

    $this->assertTrue(SoftCategory::isValid());

    SoftCategory::withTrashed()->where('name', 'Child 1')->first()->restore();

    $this->assertTrue(SoftCategory::isValid());

    $expected = array(
      $this->categories('Child 1'   , 'SoftCategory'),
      $this->categories('Child 2'   , 'SoftCategory'),
      $this->categories('Child 2.1' , 'SoftCategory'),
      $this->categories('Child 3'   , 'SoftCategory')
    );
    $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

    $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
    $this->assertEquals(10, $this->categories('Root 1', 'SoftCategory')->getRight());

    $this->assertEquals(2, $this->categories('Child 1', 'SoftCategory')->getLeft());
    $this->assertEquals(3, $this->categories('Child 1', 'SoftCategory')->getRight());

    $this->assertEquals(4, $this->categories('Child 2', 'SoftCategory')->getLeft());
    $this->assertEquals(7, $this->categories('Child 2', 'SoftCategory')->getRight());
    $this->assertEquals(5, $this->categories('Child 2.1', 'SoftCategory')->getLeft());
    $this->assertEquals(6, $this->categories('Child 2.1', 'SoftCategory')->getRight());

    $this->assertEquals(8, $this->categories('Child 3', 'SoftCategory')->getLeft());
    $this->assertEquals(9, $this->categories('Child 3', 'SoftCategory')->getRight());
  }

  public function testRestoreUnshiftsIndexesSubtree() {
    $this->assertTrue(SoftCategory::isValid());

    $this->categories('Child 2', 'SoftCategory')->delete();

    $this->assertTrue(SoftCategory::isValid());

    SoftCategory::withTrashed()->where('name', 'Child 2')->first()->restore();

    $this->assertTrue(SoftCategory::isValid());

    $expected = array(
      $this->categories('Child 1'   , 'SoftCategory'),
      $this->categories('Child 2'   , 'SoftCategory'),
      $this->categories('Child 2.1' , 'SoftCategory'),
      $this->categories('Child 3'   , 'SoftCategory')
    );
    $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

    $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
    $this->assertEquals(10, $this->categories('Root 1', 'SoftCategory')->getRight());

    $this->assertEquals(2, $this->categories('Child 1', 'SoftCategory')->getLeft());
    $this->assertEquals(3, $this->categories('Child 1', 'SoftCategory')->getRight());

    $this->assertEquals(4, $this->categories('Child 2', 'SoftCategory')->getLeft());
    $this->assertEquals(7, $this->categories('Child 2', 'SoftCategory')->getRight());
    $this->assertEquals(5, $this->categories('Child 2.1', 'SoftCategory')->getLeft());
    $this->assertEquals(6, $this->categories('Child 2.1', 'SoftCategory')->getRight());

    $this->assertEquals(8, $this->categories('Child 3', 'SoftCategory')->getLeft());
    $this->assertEquals(9, $this->categories('Child 3', 'SoftCategory')->getRight());
  }

  public function testRestoreUnshiftsIndexesFullSubtree() {
    $this->assertTrue(SoftCategory::isValid());

    $this->categories('Root 1', 'SoftCategory')->delete();

    $this->assertTrue(SoftCategory::isValid());

    SoftCategory::withTrashed()->where('name', 'Root 1')->first()->restore();

    $this->assertTrue(SoftCategory::isValid());

    $expected = array(
      $this->categories('Child 1'   , 'SoftCategory'),
      $this->categories('Child 2'   , 'SoftCategory'),
      $this->categories('Child 2.1' , 'SoftCategory'),
      $this->categories('Child 3'   , 'SoftCategory')
    );
    $this->assertEquals($expected, $this->categories('Root 1', 'SoftCategory')->getDescendants()->all());

    $this->assertEquals(1, $this->categories('Root 1', 'SoftCategory')->getLeft());
    $this->assertEquals(10, $this->categories('Root 1', 'SoftCategory')->getRight());

    $this->assertEquals(2, $this->categories('Child 1', 'SoftCategory')->getLeft());
    $this->assertEquals(3, $this->categories('Child 1', 'SoftCategory')->getRight());

    $this->assertEquals(4, $this->categories('Child 2', 'SoftCategory')->getLeft());
    $this->assertEquals(7, $this->categories('Child 2', 'SoftCategory')->getRight());
    $this->assertEquals(5, $this->categories('Child 2.1', 'SoftCategory')->getLeft());
    $this->assertEquals(6, $this->categories('Child 2.1', 'SoftCategory')->getRight());

    $this->assertEquals(8, $this->categories('Child 3', 'SoftCategory')->getLeft());
    $this->assertEquals(9, $this->categories('Child 3', 'SoftCategory')->getRight());
  }

}
