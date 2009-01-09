<?php
interface repose_IProxy {
    public function ___reposeProxyOriginalClassName();
    public function ___reposeProxySetter($prop, $value = null);
    public function ___reposeProxyGetter($prop);
    public function ___reposeProxyFromData($session, $data);
    public function ___reposeProxyClone($session, $source);
    public function ___reposeProxyPrimaryKey($session);
    public function ___reposeProxyGetProperties($session);
}
?>
