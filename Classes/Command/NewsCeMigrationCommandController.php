<?php

namespace GeorgRinger\NewsFalMigration\Command;

/**
 * This file is part of the "news_fal_migration" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Controller to import news records
 *
 */
class NewsCeMigrationCommandController extends CommandController
{

    /**
     * Array of flash messages (params) array[][status,title,message]
     *
     * @var array
     */
    protected $messageArray = [];

    /**
     * @var DatabaseConnection
     */
    protected $databaseConnection;
    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $resourceFactory;
    /**
     * @var \TYPO3\CMS\Core\Resource\Folder
     */
    protected $categoryImageFolder;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        $this->resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
    }


    /**
     * Migrate related content elements of news
     */
    public function runCommand()
    {
        $this->updateContentRelationToMm();
        $this->setParentId();
        return $this->generateOutput();
    }

    public function sortingCommand() {

      $rows = $this->databaseConnection->exec_SELECTgetRows('*', 'tx_news_domain_model_news_ttcontent_mm', '1=1');

foreach ($rows as $row) {
    $this->databaseConnection->exec_UPDATEquery(
        'tt_content',
        'deleted=0 AND uid=' . $row['uid_foreign'] . ' AND tx_news_related_news=' . $row['uid_local'],
        [
            'sorting' => $row['sorting']
        ]
    );
}
    }

    /**
     * news records got a relation to content elements and the relation uses now a mm query
     * This method allows to update the mm table to got everything in sync again
     *
     * @return void
     */
    protected function updateContentRelationToMm()
    {
        $title = 'Update tt_content relation';
        $countMmTable = $this->databaseConnection->exec_SELECTcountRows('*', 'tx_news_domain_model_news_ttcontent_mm',
            '1=1');
        $countContentElementRelation = $this->databaseConnection->exec_SELECTcountRows('*', 'tx_news_domain_model_news',
            'deleted=0 AND content_elements != ""');
        if ($countMmTable === 0 && $countContentElementRelation > 0) {
            $newsCount = 0;
            $res = $this->databaseConnection->exec_SELECTquery('uid,content_elements', 'tx_news_domain_model_news',
                'deleted=0 AND content_elements != ""');
            while ($row = $this->databaseConnection->sql_fetch_assoc($res)) {
                $newsCount++;
                $contentElementUids = explode(',', $row['content_elements']);
                $i = 1;
                foreach ($contentElementUids as $contentElement) {
                    // Insert mm relation
                    $insert = [
                        'uid_local' => $row['uid'],
                        'uid_foreign' => $contentElement,
                        'sorting' => $i++
                    ];
                    $this->databaseConnection->exec_INSERTquery('tx_news_domain_model_news_ttcontent_mm', $insert);
                }
                // Update new record
                $update = ['content_elements' => count($contentElementUids)];
                $this->databaseConnection->exec_UPDATEquery('tx_news_domain_model_news', 'uid=' . $row['uid'], $update);
            }
            $this->databaseConnection->sql_free_result($res);
            $this->messageArray[] = [FlashMessage::OK, $title, $newsCount . ' news records have been updated!'];
        } else {
            $this->messageArray[] = [
                FlashMessage::NOTICE,
                $title,
                'Not needed/possible anymore as the mm table is already filled!'
            ];
        }
    }

    protected function setParentId()
    {
        $res = $this->databaseConnection->exec_SELECTquery('*', 'tx_news_domain_model_news_ttcontent_mm',
            '1=1');
        while ($row = $this->databaseConnection->sql_fetch_assoc($res)) {
            $update = ['tx_news_related_news' => $row['uid_local']];
            $this->databaseConnection->exec_UPDATEquery('tt_content', 'uid=' . $row['uid_foreign'], $update);
        }
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function generateOutput()
    {
        $output = '';
        foreach ($this->messageArray as $messageItem) {
//            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
//            $flashMessage = GeneralUtility::makeInstance(
//                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
//                $messageItem[2],
//                $messageItem[1],
//                $messageItem[0]);
//            $output .= $flashMessage->render();

            print_r($messageItem);
        }
        return $output;
    }


}
