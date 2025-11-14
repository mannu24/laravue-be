<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:vapid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys for Web Push notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating VAPID keys using OpenSSL...');
        
        // Use OpenSSL command line (more reliable)
        $tempDir = sys_get_temp_dir();
        $privateKeyFile = $tempDir . '/vapid_private_' . uniqid() . '.pem';
        $publicKeyFile = $tempDir . '/vapid_public_' . uniqid() . '.pem';
        
        try {
            // Generate private key
            $command = "openssl ecparam -genkey -name prime256v1 -noout -out \"{$privateKeyFile}\" 2>&1";
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0 || !file_exists($privateKeyFile)) {
                $this->error('Failed to generate private key. Make sure OpenSSL is installed.');
                $this->info('Output: ' . implode("\n", $output));
                $this->info('Please use an online VAPID key generator: https://web-push-codelab.glitch.me/');
                return 1;
            }
            
            // Extract public key
            $command = "openssl ec -in \"{$privateKeyFile}\" -pubout -out \"{$publicKeyFile}\" 2>&1";
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0 || !file_exists($publicKeyFile)) {
                @unlink($privateKeyFile);
                $this->error('Failed to extract public key.');
                $this->info('Output: ' . implode("\n", $output));
                return 1;
            }
            
            // Read keys
            $privateKeyPEM = file_get_contents($privateKeyFile);
            $publicKeyPEM = file_get_contents($publicKeyFile);
            
            // Parse public key (extract 65-byte uncompressed point)
            $publicKeyDer = $this->pemToDer($publicKeyPEM);
            $publicKeyRaw = $this->extractPublicKeyFromDer($publicKeyDer);
            
            if (!$publicKeyRaw || strlen($publicKeyRaw) !== 65) {
                @unlink($privateKeyFile);
                @unlink($publicKeyFile);
                $this->error('Failed to parse public key. Invalid format.');
                return 1;
            }
            
            // Parse private key (extract 32-byte private key value)
            $privateKeyDer = $this->pemToDer($privateKeyPEM, true);
            $privateKeyRaw = $this->extractPrivateKeyFromDer($privateKeyDer);
            
            if (!$privateKeyRaw || strlen($privateKeyRaw) !== 32) {
                @unlink($privateKeyFile);
                @unlink($publicKeyFile);
                $this->error('Failed to parse private key. Invalid format.');
                return 1;
            }
            
            // Encode in URL-safe base64 (without padding)
            $publicKey = $this->base64UrlEncode($publicKeyRaw);
            $privateKey = $this->base64UrlEncode($privateKeyRaw);
            
            // Clean up
            @unlink($privateKeyFile);
            @unlink($publicKeyFile);
            
            $this->info('VAPID keys generated successfully!');
            $this->newLine();
            $this->line('Add these to your .env file:');
            $this->newLine();
            $this->line('VAPID_PUBLIC_KEY=' . $publicKey);
            $this->line('VAPID_PRIVATE_KEY=' . $privateKey);
            $this->line('VAPID_SUBJECT=' . config('app.url'));
            $this->newLine();
            $this->info('Keys are in URL-safe base64 format (no padding).');
            
            return 0;
        } catch (\Exception $e) {
            @unlink($privateKeyFile);
            @unlink($publicKeyFile);
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Convert PEM to DER format
     */
    protected function pemToDer($pem, $isPrivate = false)
    {
        $header = $isPrivate ? '-----BEGIN EC PRIVATE KEY-----' : '-----BEGIN PUBLIC KEY-----';
        $footer = $isPrivate ? '-----END EC PRIVATE KEY-----' : '-----END PUBLIC KEY-----';
        
        $pem = str_replace([$header, $footer, "\n", "\r", ' '], '', $pem);
        return base64_decode($pem);
    }
    
    /**
     * Extract public key from DER (65-byte uncompressed point)
     */
    protected function extractPublicKeyFromDer($der)
    {
        // Find the 0x04 byte which indicates uncompressed point
        $offset = strpos($der, "\x04");
        if ($offset !== false && strlen($der) >= $offset + 65) {
            return substr($der, $offset, 65);
        }
        return null;
    }
    
    /**
     * Extract private key from DER (32-byte private key value)
     */
    protected function extractPrivateKeyFromDer($der)
    {
        // EC private key DER structure: SEQUENCE { version, privateKey, parameters, publicKey }
        // The private key is usually the second OCTET STRING
        // Simplified: look for a 32-byte sequence
        $offset = strpos($der, "\x04\x20"); // OCTET STRING with 32 bytes
        if ($offset !== false && strlen($der) >= $offset + 34) {
            return substr($der, $offset + 2, 32);
        }
        // Alternative: last 32 bytes might be the key
        if (strlen($der) >= 32) {
            return substr($der, -32);
        }
        return null;
    }


    /**
     * Base64 URL encode (without padding)
     */
    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
