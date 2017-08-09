----------------------
Parz: Quickbox PHP Parser
----------------------
##Features 
 - Parse .IIF format files.
 - Ability to use any other type of files extensions, as long as you're using .iif file structure.
 - Ability to parse multiple types of records in the same file. 
 - Ability to customize headers prefix and header postfix character. 
 - Ability to customize your file separator. 
    - Tab is the default separator type. 

----------------------
Example  
----------------------

###Data to be parsed (data in file)
```sh
!TRNS	TRNSID	TRNSTYPE	DATE	ACCNT	NAME	AMOUNT	DOCNUM	MEMO	CLEAR	TOPRINT
!SPL	SPLID	TRNSTYPE	DATE	ACCNT	NAME	AMOUNT	DOCNUM	MEMO	CLEAR	QNTY
!ENDTRNS
TRNS	WEFDS234CT5	BILLPMT	7/16/98	Checking	Bayshore	Service	35	Test Memo	N	Y
SPL	AA223D	BILLSPL	7/16/98	Accounts	Payable	35	CalOil	Test inner memo		-35
SPL	BB34DF	BILLSSPL	8/7/2017	Accounts two	Payable	20	oilCal	Test inner memo2		-60
ENDTRNS
!TRNS	TRNSID	TRNSTYPE	DATE	ACCNT	NAME	AMOUNT	DOCNUM	MEMO	CLEAR	TOPRINT
!ENDTRNS
TRNS	WSEDEDEDEDE	BILLKMN	7/1/2000	Checking	Bayshore2	Service	50	Test Memo 2	Y	N
ENDTRNS
```

###Parsed data

        [0] => Array
                (
                    [headers] => Array
                        (
                            [!TRNS] => TRNS
                            [TRNSID] => WEFDS234CT5
                            [TRNSTYPE] => BILLPMT
                            [DATE] => 7/16/98
                            [ACCNT] => Checking
                            [NAME] => Bayshore
                            [AMOUNT] => Service
                            [DOCNUM] => 35
                            [MEMO] => Test Memo
                            [CLEAR] => N
                            [TOPRINT] => Y
                        )
        
                    [nested] => Array
                        (
                            [0] => Array
                                (
                                    [!SPL] => SPL
                                    [SPLID] => AA223D
                                    [TRNSTYPE] => BILLSPL
                                    [DATE] => 7/16/98
                                    [ACCNT] => Accounts
                                    [NAME] => Payable
                                    [AMOUNT] => 35
                                    [DOCNUM] => CalOil
                                    [MEMO] => Test inner memo
                                    [CLEAR] =>
                                    [QNTY] => -35
                                )
        
                            [1] => Array
                                (
                                    [!SPL] => SPL
                                    [SPLID] => BB34DF
                                    [TRNSTYPE] => BILLSSPL
                                    [DATE] => 8/7/2017
                                    [ACCNT] => Accounts two
                                    [NAME] => Payable
                                    [AMOUNT] => 20
                                    [DOCNUM] => oilCal
                                    [MEMO] => Test inner memo2
                                    [CLEAR] =>
                                    [QNTY] => -60
                                )
        
                        )
        
                )
        
            [1] => Array
                (
                    [headers] => Array
                        (
                            [!TRNS] => TRNS
                            [TRNSID] => WSEDEDEDEDE
                            [TRNSTYPE] => BILLKMN
                            [DATE] => 7/1/2000
                            [ACCNT] => Checking
                            [NAME] => Bayshore2
                            [AMOUNT] => Service
                            [DOCNUM] => 50
                            [MEMO] => Test Memo 2
                            [CLEAR] => Y
                            [TOPRINT] => N
                        )
        
                    [nested] => Array
                        (
                        )
        
                )
