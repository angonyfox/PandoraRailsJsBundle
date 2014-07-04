<?php

namespace Pandora\RailsJsBundle\Command;

// use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
// use Symfony\Component\Console\Input\InputArgument;
// use Symfony\Component\Console\Input\InputInterface;
// use Symfony\Component\Console\Input\InputOption;
// use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Composer\Script\Event;

class ScriptHandler // extends ContainerAwareCommand
{
    public static $targetPath = DIRECTORY_SEPARATOR . "Resources" . DIRECTORY_SEPARATOR . "public". DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR;
    /**
     * Checks symlink's existence.
     *
     * @param string  $symlinkTarget The Target
     * @param string  $symlinkName   The Name
     * @param boolean $forceSymlink  Force to be a link or throw exception
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public static function checkSymlink($symlinkTarget, $symlinkName, $forceSymlink = false)
    {
        if ($forceSymlink && file_exists($symlinkName) && !is_link($symlinkName)) {
            if ("link" != filetype($symlinkName)) {
                throw new \Exception($symlinkName . " exists and is no link!");
            }
        } elseif (is_link($symlinkName)) {
            $linkTarget = readlink($symlinkName);
            if ($linkTarget != $symlinkTarget) {
                if (!$forceSymlink) {
                    throw new \Exception(sprintf('Symlink "%s" points to "%s" instead of "%s"', $symlinkName, $linkTarget, $symlinkTarget));
                }
                unlink($symlinkName);

                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Create the symlink.
     *
     * @param string $symlinkTarget The Target
     * @param string $symlinkName   The Name
     *
     * @throws \Exception
     */
    public static function createSymlink($symlinkTarget, $symlinkName)
    {
        if (false === @symlink($symlinkTarget, $symlinkName)) {
            throw new \Exception("An error occurred while creating symlink" . $symlinkName);
        }
        if (false === $target = readlink($symlinkName)) {
            throw new \Exception("Symlink $symlinkName points to target $target");
        }
    }

    /**
     * Create the directory mirror.
     *
     * @param string $symlinkTarget The Target
     * @param string $symlinkName   The Name
     *
     * @throws \Exception
     */
    public static function createMirror($symlinkTarget, $symlinkName)
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($symlinkName);
        $filesystem->mirror(
            realpath($symlinkTarget . DIRECTORY_SEPARATOR ),
            $symlinkName,
            null,
            array('copy_on_windows' => true, 'delete' => true, 'override' => true)
        );
    }

    public static function postInstallSymlink(Event $event)
    {
      $IO = $event->getIO();
      $IO->write(getcwd());
      $symlinkTarget = "";
      $symlinkName = self::$targetPath . "jquery-ujs.js";

      $IO->write("Checking Symlink", FALSE);
      // if (false === self::checkSymlink($symlinkTarget, $symlinkName, true)) {
      //     $IO->write("Creating Symlink: " . $symlinkName, FALSE);
      //     self::createSymlink($symlinkTarget, $symlinkName);
      // }
      $IO->write(" ... <info>OK</info>");

    }

    // protected function configure()
    // {
    //   $this
    //     ->setDescription("Check and if possible install symlink to " . static::$targetSuffix)
    //     ->addOption('no-symlink', null, InputOption::VALUE_NONE, 'Use hard copy/mirroring instead of symlink. This is required for Windows without administrator privileges.');
    // }
    //
    // protected function execute(InputInterface $input, OutputInterface $output)
    // {
    //   $this->input = $input;
    //   $this->output = $output;
    //
    //   // $symlinkTarget
    //   // $symlinkName
    //   // Automatically detect if on Win XP where symlink will allways fail
    //   if ($input->getOption('no-symlink') || PHP_OS == "WINNT") {
    //       $this->output->write("Checking destination");
    //
    //       if (true === self::checkSymlink($symlinkTarget, $symlinkName)) {
    //           $this->output->writeln(" ... <comment>symlink already exists</comment>");
    //       } else {
    //           $this->output->writeln(" ... <comment>not existing</comment>");
    //           $this->output->writeln("Mirroring from: " . $symlinkName);
    //           $this->output->write("for target: " . $symlinkTarget);
    //           self::createMirror($symlinkTarget, $symlinkName);
    //       }
    //   } else {
    //       $this->output->write("Checking symlink");
    //       if (false === self::checkSymlink($symlinkTarget, $symlinkName, true)) {
    //           $this->output->writeln(" ... <comment>not existing</comment>");
    //           $this->output->writeln("Creating symlink: " . $symlinkName);
    //           $this->output->write("for target: " . $symlinkTarget);
    //           self::createSymlink($symlinkTarget, $symlinkName);
    //       }
    //   }
    //
    //   $this->output->writeln(" ... <info>OK</info>");
    // }
}
