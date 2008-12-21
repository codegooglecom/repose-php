<?php
interface repose_IProxy {
    public function ___reposeProxyOriginalClassName();
    public function ___reposeProxySetter($prop, $value = null);
    public function ___reposeProxyGetter($prop);
    public function ___reposeProxyFromData($data);
    public function ___reposeProxyClone($source);
    public function ___reposeProxyPrimaryKey();
    public function ___reposeProxyGetProperties();
}
?>
