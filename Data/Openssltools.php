<?php
/* Open SSL Utils
 * @copyright   Bill Rocha - http://plus.google.com/+BillRocha
 * @license     MIT & GLP2
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.2.1
 * @package     Neos\Data
 * @access      public
 * @since       0.0.4
*/

namespace Neos\Data;

class Openssltools
{
    private $configKeysPath = null;
    private $fncert = null;
    private $fnsscert = null;
    private $fnprivate = null;
    private $fnpublic = null;
    private $privkey = null;

    function __construct()
    {
        $this->configKeysPath = _PHP.'Config/keys/';
        $this->fncert = $this->configKeysPath.'certificate.crt';
        $this->fnsscert = $this->configKeysPath.'self_signed_certificate.cer';
        $this->fnprivate = $this->configKeysPath.'private.key';
        $this->fnpublic = $this->configKeysPath.'public.key';
    }


    //Gerador de certificados e chaves pública e privada. 
    function createKeys()
    {
        $SSLcnf = [];
        $dn = [];

        //get configurations
        include $this->configKeysPath.'openssl.php';

        // Generate a new private (and public) key pair
        $this->privkey = openssl_pkey_new($SSLcnf);

        // Generate a certificate signing request
        $csr = openssl_csr_new($dn, $this->privkey, $SSLcnf);

        // You will usually want to create a self-signed certificate at this
        // point until your CA fulfills your request.
        // This creates a self-signed cert that is valid for 365 days
        $sscert = openssl_csr_sign($csr, null, $this->privkey, 365, $SSLcnf);

        // Now you will want to preserve your private key, CSR and self-signed
        // cert so that they can be installed into your web server, mail server
        // or mail client (depending on the intended use of the certificate).
        // This example shows how to get those things into variables, but you
        // can also store them directly into files.
        // Typically, you will send the CSR on to your CA who will then issue
        // you with the "real" certificate.

        //CERTIFICADO
        openssl_csr_export_to_file($csr, $this->fncert, false);

        //CERTIFICADO AUTO-ASSINADO
        openssl_x509_export_to_file($sscert, $this->fnsscert, false);

        //CHAVE PRIVADA (private.pem)
        openssl_pkey_export_to_file($this->privkey , $this->fnprivate, null, $SSLcnf);

        //CHAVE PÚBLICA (public.key)
        return file_put_contents($this->fnpublic, openssl_pkey_get_details($this->privkey)['key']);
    }

}