<?php
namespace Craft;

/**
 * Imager by André Elvan
 *
 * @author      André Elvan <http://vaersaagod.no>
 * @package     Imager
 * @copyright   Copyright (c) 2016, André Elvan
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @link        https://github.com/aelvan/Imager-Craft
 */

class Imager_JpegoptimTask extends BaseTask
{
    private $_paths;

    public function getDescription()
    {
        return Craft::t('Optimizing images with jpegoptim');
    }

    /**
     * @inheritDoc ITask::getTotalSteps()
     *
     * @return int
     */
    public function getTotalSteps()
    {
        // Get the actual paths out of the settings
        $this->_paths = $this->getSettings()->paths;

        // Count our final chunked array
        return is_array($this->_paths) ? count($this->_paths) : 1;
    }

    /**
     * @inheritDoc ITask::runStep()
     *
     * @param int $step
     *
     * @return bool
     */
    public function runStep($step)
    {
        if (is_array($this->_paths)) {
            $path = $this->_paths[$step];
        } else {
            $path = $this->_paths;
        }

        // Run TinyPNG 
        craft()->imager->runJpegoptim($path);
        
        // if AWS is enabled, upload file
        if (craft()->imager->getSetting('awsEnabled')) {
            try {
                craft()->imager->uploadToAWS($path);
            } catch (\Exception $e) {
                ImagerPlugin::log("Upload to AWS failed for $path in Imager_JpegoptimTask", LogLevel::Error);
            }
        }

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc BaseSavableComponentType::defineSettings()
     *
     * @return array
     */
    protected function defineSettings()
    {
        return array(
          'paths' => AttributeType::Mixed
        );
    }

}