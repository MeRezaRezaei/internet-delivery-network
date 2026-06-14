<?php

namespace App\Services\Xray;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class XrayValidator
{
    public function validate(array $config): array
    {
        $tempFile = storage_path('app/temp_xray_' . Str::random(10) . '.json');
        file_put_contents($tempFile, json_encode($config, JSON_PRETTY_PRINT));

        $result = Process::run(['xray', '-test', '-config', $tempFile]);

        unlink($tempFile);

        $output = $result->output() . $result->errorOutput();
        $success = str_contains($output, 'Configuration OK');

        return [
            'success' => $success,
            'output' => $output,
        ];
    }
}
