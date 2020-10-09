<?php
namespace App\Command;

use App\Entity\File;
use Mediashare\CloudFile\CloudFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CloudfileMigrationCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'cloudfile:migration';
    private $container;

    public function __construct(ContainerInterface $container) {
        parent::__construct();
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->stockage = $this->container->getParameter('stockage');
    }

    protected function configure() {
        $this->setDescription('Check if file exist on CloudFile plateform, and switch download url.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->output = $output;
        foreach ($this->em->getRepository(File::class)->findAll() as $file):
            $output->write($file->getFilename());
            if (empty($file->getMetadata()['cloudfile'])):
                $cloudfile = $this->upload($file);
                if ($cloudfile):
                    $metadata = $file->getMetadata();
                    $metadata['cloudfile'] = $cloudfile;
                    $file->setMetadata($metadata);
                    $this->em->persist($file);
                    $output->write(' - Updated');
                endif;
            endif;
            $output->writeln('');
        endforeach;
        $this->em->flush();
        return 0;
    }

    protected function upload(File $file) {
        $duplicate = $this->duplicate($file->getChecksum());
        if ($duplicate): return $duplicate; endif;

        \copy($this->stockage.'/'.$file->getPath(), $realFile = $this->stockage.'/'.$file->getFilename());
        $cloudfile = new CloudFile('https://api.cloudfile.tech', '1FizpPryGEb54URKYZetHSn90DAWl28O');
        $upload = $cloudfile->file()->upload($realFile, ['description' => $file->getMetadata()['description'], 'hub' => $file->getHub()->getName()]);
        \unlink($realFile);
        if ($upload['status'] === 'success'):
            $this->output->whrite(' - Uploaded');
            return 'https://cloudfile.tech/public/5f801669a7704/'.$upload['files']['results'][0]['id'];
        else: return false; endif;
    }

    protected function duplicate(string $checksum) {
        $cloudfile = new CloudFile();
        $duplicate = $cloudfile->file()->search('checksum='.$checksum);
        if ($duplicate['files']['counter'] != 0):
            $result = $duplicate['files']['results'][0];
            $this->output->whrite(' - Verified');
            return 'https://cloudfile.tech/public/'.$result['volume']['id'].'/'.$result['file']['id'];
        else: return false; endif;
    }
}