<?php

/**
 * @author Khoa Bui (khoaofgod)  <khoaofgod@gmail.com> https://www.phpfastcache.com
 * @author Georges.L (Geolim4)  <contact@geolim4.com>
 */

use Phpfastcache\CacheManager;
use Phpfastcache\Core\Pool\TaggableCacheItemPoolInterface;
use Phpfastcache\Tests\Helper\TestHelper;

chdir(__DIR__);
require_once __DIR__ . '/../vendor/autoload.php';
$testHelper = new TestHelper('Items tags features');
$defaultDriver = (!empty($argv[ 1 ]) ? ucfirst($argv[ 1 ]) : 'Files');
$driverInstance = CacheManager::getInstance($defaultDriver);

/**
 * Item tag test // Init tags/items
 */

$createItemsCallback = static function() use ($driverInstance)
{
    $item1 = $driverInstance->getItem('tag-test1');
    $item2 = $driverInstance->getItem('tag-test2');
    $item3 = $driverInstance->getItem('tag-test3');

    $item1->set('item-test_1')
      ->expiresAfter(600)
      ->addTag('tag-test_1')
      ->addTag('tag-test_all')
      ->addTag('tag-test_all2')
      ->addTag('tag-test_all3');

    $item2->set('item-test_2')
      ->expiresAfter(600)
      ->addTag('tag-test_1')
      ->addTag('tag-test_2')
      ->addTag('tag-test_all')
      ->addTag('tag-test_all2')
      ->addTag('tag-test_all3');

    $item3->set('item-test_3')
      ->expiresAfter(600)
      ->addTag('tag-test_1')
      ->addTag('tag-test_2')
      ->addTag('tag-test_3')
      ->addTag('tag-test_all')
      ->addTag('tag-test_all2')
      ->addTag('tag-test_all3')
      ->addTag('tag-test_all4');

    $driverInstance->saveMultiple($item1, $item2, $item3);

    return [
      'item1' => $item1,
      'item2' => $item2,
      'item3' => $item3
    ];
};

/**
 * Item tag test // Step 1
 */
$testHelper->printNewLine()->printText('#1 Testing getter: getItemsByTag() with strategy TAG_STRATEGY_ONE // Expecting 3 results');
$createItemsCallback();

$tagsItems = $driverInstance->getItemsByTag('tag-test_all');
if(is_array($tagsItems))
{
    if(count($tagsItems) === 3)
    {
        foreach($tagsItems as $tagsItem)
        {
            if(!in_array($tagsItem->getKey(), ['tag-test1', 'tag-test2', 'tag-test3'])){
                $testHelper->assertFail('STEP#1 // Got unexpected tagged item key:' . $tagsItem->getKey());
                goto itemTagTest2;
            }
        }
        $testHelper->assertPass('STEP#1 // Successfully retrieved 3 tagged item keys');
    }
    else
    {
        $testHelper->assertFail('STEP#1 //Got wrong count of item:' . count($tagsItems));
        goto itemTagTest2;
    }
}
else
{
    $testHelper->assertFail('STEP#1 // Expected $tagsItems to be an array, got: ' . gettype($tagsItems));
    goto itemTagTest2;
}

/**
 * Item tag test // Step 2
 */
itemTagTest2:
$testHelper->printNewLine()->printText('#2 Testing getter: getItemsByTags() with strategy TAG_STRATEGY_ALL // Expecting 3 results');
$driverInstance->deleteItems(['item-test_1', 'item-test_2', 'item-test_3']);
$createItemsCallback();

$tagsItems = $driverInstance->getItemsByTags(['tag-test_all', 'tag-test_all2', 'tag-test_all3'], TaggableCacheItemPoolInterface::TAG_STRATEGY_ALL);

if(is_array($tagsItems))
{
    if(count($tagsItems) === 3)
    {
        foreach($tagsItems as $tagsItem)
        {
            if(!in_array($tagsItem->getKey(), ['tag-test1', 'tag-test2', 'tag-test3'])){
                $testHelper->assertFail('STEP#2 // Got unexpected tagged item key:' . $tagsItem->getKey());
                goto itemTagTest3;
            }
        }
        $testHelper->assertPass('STEP#2 // Successfully retrieved 3 tagged item key');
    }
    else
    {
        $testHelper->assertFail('STEP#2 // Got wrong count of item:' . count($tagsItems) );
        goto itemTagTest3;
    }
}
else
{
    $testHelper->assertFail('STEP#2 // Expected $tagsItems to be an array, got: ' . gettype($tagsItems));
    goto itemTagTest3;
}

/**
 * Item tag test // Step 3
 */
itemTagTest3:
$testHelper->printNewLine()->printText('#3 Testing getter: getItemsByTags() with strategy TAG_STRATEGY_ALL // Expecting 1 result');
$driverInstance->deleteItems(['item-test_1', 'item-test_2', 'item-test_3']);
$createItemsCallback();

$tagsItems = $driverInstance->getItemsByTags(['tag-test_all', 'tag-test_all2', 'tag-test_all3', 'tag-test_all4'], TaggableCacheItemPoolInterface::TAG_STRATEGY_ALL);

if(is_array($tagsItems))
{
    if(count($tagsItems) === 1)
    {
        if(isset($tagsItems['tag-test3']))
        {
            if($tagsItems['tag-test3']->getKey() !== 'tag-test3'){
                $testHelper->assertFail('STEP#3 // Got unexpected tagged item key:' . $tagsItems['tag-test3']->getKey());
                goto itemTagTest4;
            }
            $testHelper->assertPass('STEP#3 // Successfully retrieved 1 tagged item keys');
        }
        else
        {
            $testHelper->assertFail('STEP#3 // Got wrong array key, expected "tag-test3", got "' . key($tagsItems) . '"');
            goto itemTagTest4;
        }
    }
    else
    {
        $testHelper->assertFail('STEP#3 // Got wrong count of item:' . count($tagsItems));
        goto itemTagTest4;
    }
}
else
{
    $testHelper->assertFail('STEP#3 // Expected $tagsItems to be an array, got: ' . gettype($tagsItems));
    goto itemTagTest4;
}

/**
 * Item tag test // Step 4
 */
itemTagTest4:
$testHelper->printNewLine()->printText('#4 Testing deleter: deleteItemsByTag() // Expecting no item left');
$driverInstance->deleteItems(['item-test_1', 'item-test_2', 'item-test_3']);
$createItemsCallback();
$driverInstance->deleteItemsByTag('tag-test_all');

if(count($driverInstance->getItemsByTag('tag-test_all')) > 0)
{
    $testHelper->assertFail('[FAIL] STEP#4 // Getter getItemsByTag() found item(s), possible memory leak');
}
else
{
    $testHelper->assertPass('STEP#4 // Getter getItemsByTag() found no item');
}

$i = 0;
while(++$i <= 3)
{
    if($driverInstance->getItem("tag-test{$i}")->isHit())
    {
        $testHelper->assertFail("STEP#4 // Item 'tag-test{$i}' should've been deleted and is still in cache storage");
    }
    else
    {
        $testHelper->assertPass("STEP#4 // Item 'tag-test{$i}' have been deleted and is no longer in cache storage");
    }
}

/**
 * Item tag test // Step 5
 */
itemTagTest5:
$testHelper->printNewLine()->printText('#5 Testing deleter: deleteItemsByTags() with strategy TAG_STRATEGY_ALL // Expecting no item left');
$driverInstance->deleteItems(['item-test_1', 'item-test_2', 'item-test_3']);
$createItemsCallback();

$driverInstance->deleteItemsByTags(['tag-test_all', 'tag-test_all2', 'tag-test_all3'], TaggableCacheItemPoolInterface::TAG_STRATEGY_ALL);

if(count($driverInstance->getItemsByTag('tag-test_all')) > 0)
{
    $testHelper->assertFail('STEP#5 // Getter getItemsByTag() found item(s), possible memory leak');
}
else
{
    $testHelper->assertPass('STEP#5 // Getter getItemsByTag() found no item');
}

$i = 0;
while(++$i <= 3)
{
    if($driverInstance->getItem("tag-test{$i}")->isHit())
    {
        $testHelper->assertFail("STEP#5 // Item 'tag-test{$i}' should've been deleted and is still in cache storage");
    }
    else
    {
        $testHelper->assertPass("STEP#5 // Item 'tag-test{$i}' have been deleted and is no longer in cache storage");
    }
}

/**
 * Item tag test // Step 6
 */
itemTagTest6:
$testHelper->printNewLine()->printText('#6 Testing deleter: deleteItemsByTags() with strategy TAG_STRATEGY_ALL // Expecting a specific item to be deleted');
$driverInstance->deleteItems(['item-test_1', 'item-test_2', 'item-test_3']);
$createItemsCallback();

/**
 *  Only item 'item-test_3' got all those tags
 */
$driverInstance->deleteItemsByTags(['tag-test_all', 'tag-test_all2', 'tag-test_all3', 'tag-test_all4'], TaggableCacheItemPoolInterface::TAG_STRATEGY_ALL);

if($driverInstance->getItem('item-test_3')->isHit())
{
    $testHelper->assertFail('STEP#6 // Getter getItem() found item \'item-test_3\', possible memory leak');
}
else
{
    $testHelper->assertPass('STEP#6 // Getter getItem() did not found item \'item-test_3\'');
}

/**
 * Item tag test // Step 7
 */
itemTagTest7:
$testHelper->printNewLine()->printText('#7 Testing appender: appendItemsByTags() with strategy TAG_STRATEGY_ALL // Expecting items value to get an appended part of string');
$driverInstance->deleteItems(['item-test_1', 'item-test_2', 'item-test_3']);
$createItemsCallback();
$appendStr = '$*#*$';
$driverInstance->appendItemsByTags(['tag-test_all', 'tag-test_all2', 'tag-test_all3'], $appendStr, TaggableCacheItemPoolInterface::TAG_STRATEGY_ALL);

foreach($driverInstance->getItems(['tag-test1', 'tag-test2', 'tag-test3']) as $item)
{
    if(strpos($item->get(), $appendStr) === false)
    {
        $testHelper->assertFail("STEP#7 // Item '{$item->getKey()}' does not have the string part '{$appendStr}' in it's value");
    }
    else
    {
        $testHelper->assertPass("STEP#7 // Item 'tag-test{$item->getKey()}' does have the string part '{$appendStr}' in it's value");
    }
}

/**
 * Item tag test // Step 7
 */
itemTagTest8:
$testHelper->printNewLine()->printText('#8 Testing prepender: prependItemsByTags() with strategy TAG_STRATEGY_ALL // Expecting items value to get a prepended part of string');
$driverInstance->deleteItems(['item-test_1', 'item-test_2', 'item-test_3']);
$createItemsCallback();
$prependStr = '&+_+&';
$driverInstance->prependItemsByTags(['tag-test_all', 'tag-test_all2', 'tag-test_all3'], $prependStr, TaggableCacheItemPoolInterface::TAG_STRATEGY_ALL);

foreach($driverInstance->getItems(['tag-test1', 'tag-test2', 'tag-test3']) as $item)
{
    if(strpos($item->get(), $prependStr) === false)
    {
        $testHelper->assertFail("STEP#8 // Item '{$item->getKey()}' does not have the string part '{$prependStr}' in it's value");
    }
    else
    {
        $testHelper->assertPass("STEP#8 // Item 'tag-test{$item->getKey()}' does have the string part '{$prependStr}' in it's value");
    }
}



/**
 * Item tag test // Step 9
 */
itemTagTest9:
$testHelper->printNewLine()->printText('#9 Testing getter: getItemsByTags() with strategy TAG_STRATEGY_ONLY // Expecting 1 result');
$driverInstance->clear();
$createItemsCallback();

$tagsItems = $driverInstance->getItemsByTags(['tag-test_1', 'tag-test_all', 'tag-test_all2', 'tag-test_all3'], TaggableCacheItemPoolInterface::TAG_STRATEGY_ONLY);

if(is_array($tagsItems))
{
    if(count($tagsItems) === 1)
    {
        if(isset($tagsItems['tag-test1']))
        {
            if($tagsItems['tag-test1']->getKey() !== 'tag-test1'){
                $testHelper->assertFail('STEP#9 // Got unexpected tagged item key:' . $tagsItems['tag-test1']->getKey());
                goto itemTagTest10;
            }
            $testHelper->assertPass('STEP#9 // Successfully retrieved 1 tagged item keys');
        }
        else
        {
            $testHelper->assertFail('STEP#9 // Got wrong array key, expected "tag-test3", got "' . key($tagsItems) . '"');
            goto itemTagTest10;
        }
    }
    else
    {
        $testHelper->assertFail('STEP#9 // Got wrong count of item:' . count($tagsItems));
        goto itemTagTest10;
    }
}
else
{
    $testHelper->assertFail('STEP#9 // Expected $tagsItems to be an array, got: ' . gettype($tagsItems));
    goto itemTagTest10;
}

/**
 * Item tag test // Step 10
 */
itemTagTest10:
$testHelper->printNewLine()->printText('#10 Testing getter: getItemsByTags() with strategy TAG_STRATEGY_ONLY // Expecting 0 result');
$driverInstance->clear();
$createItemsCallback();

$tagsItems = $driverInstance->getItemsByTags(['tag-test_1', 'tag-test_all', 'tag-test_all2', /*'tag-test_all3'*/], TaggableCacheItemPoolInterface::TAG_STRATEGY_ONLY);

if(is_array($tagsItems))
{
    if(count($tagsItems) === 0)
    {
        $testHelper->assertPass('STEP#10 // Successfully retrieved 0 tagged item keys');
    }
    else
    {
        $testHelper->assertFail('STEP#10 // Got wrong count of item:' . count($tagsItems));
        goto itemTagTest11;
    }
}
else
{
    $testHelper->assertFail('STEP#10 // Expected $tagsItems to be an array, got: ' . gettype($tagsItems));
    goto itemTagTest11;
}

itemTagTest11:
$testHelper->terminateTest();
