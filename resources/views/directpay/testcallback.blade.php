<form action="http://10.131.72.38:8000/directpayReceipt" method="post">
    @csrf
    <input type="hidden" name="Fpx_SellerOrderNo" value="OrderS_20240527102204" />
    <input type="hidden" name="TransactionAmount" value="1.00" />
    <input type="hidden" name="Fpx_SellerExOrderNo" value="DirectPayTest20231227201201" />
    <input type="hidden" name="Fpx_DebitAuthCode" value="00" />
    <input type="hidden" name="Fpx_BuyerBankBranch" value="BANK ISLAM" />
    <input type="hidden" name="Fpx_FpxTxnId" value="2311151056590237" />
    <input type="hidden" name="DateTime" value="11/15/2023 1:57:08 PM" />
    <input type="submit" value="Submit" />
</form>