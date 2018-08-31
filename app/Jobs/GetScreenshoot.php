<?php

namespace App\Jobs;

use App\Domain;
use App\Providers;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class GetScreenshoot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $object;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() // ho un unico Job che uso per il modello Domains e Providers
    {
        try {

            if($this->object instanceof Domain){
                $folder = config('custompath.domains');
                $url = $this->object->url;
            }

            if($this->object instanceof Providers){
                $folder = config('custompath.providers');
                $url = $this->object->website;
            }

            if(!Storage::exists($folder)) Storage::makeDirectory($folder);

            $path = $folder . '/' . uniqid() . ".png";
            //"Fit should be one of `contain`, `max`, `fill`, `stretch`, `crop`"

            Browsershot::url($url)
                ->dismissDialogs()
                ->waitUntilNetworkIdle()
                ->windowSize(1920, 1080)
                ->fit('fill', 640, 640)
                ->save(public_path() . '/storage/' . $path);

            $this->object->update(['screenshoot' => $path]); // setto il nuovo path a db

        }catch (\Exception $e){
            //logger('Errore creazione screenshoot: ' . $e);
        }
    }
}