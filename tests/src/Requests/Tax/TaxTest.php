<?php
/**
 * Created by PhpStorm.
 * User: ycoutu
 * Date: 11/07/18
 * Time: 4:09 PM
 */

namespace ZEROSPAM\Freshbooks\Test\Requests\Tax;

use Carbon\Carbon;
use ZEROSPAM\Framework\SDK\Test\Base\TestCase;
use ZEROSPAM\Freshbooks\Request\Call\Tax\GetTaxListRequest;
use ZEROSPAM\Freshbooks\Request\Call\Tax\GetTaxRequest;
use ZEROSPAM\Freshbooks\Response\Tax\TaxResponse;

class TaxTest extends TestCase
{
    public function testGetTax(): void
    {
        $jsonResponse = <<<JSON
{
    "response": {
        "result": {
            "tax": {
                "accounting_systemid": "Muu5d",
                "updated": "2018-07-11 09:30:12",
                "name": "TAX",
                "taxid": 3334,
                "number": "123",
                "amount": "9.5",
                "compound": false,
                "id": 3334
            }
        }
    }
}
JSON;
        $client = $this->preSuccess($jsonResponse);
        $request = (new GetTaxRequest)
            ->setAccountId("Muu5d")
            ->setTaxId("3334");
        $client->getOAuthTestClient()
            ->processRequest($request);

        $response = $request->getResponse();

        $this->assertEquals("Muu5d", $response->accounting_systemid);
        $this->assertInstanceOf(Carbon::class, $response->updated);
        $this->assertEquals("2018-07-11", $response->updated->toDateString());
        $this->assertEquals("TAX", $response->name);
        $this->assertEquals("123", $response->number);
        $this->assertEquals(3334, $response->taxid);
        $this->assertEquals("9.5", $response->amount);
        $this->assertFalse($response->compound);
        $this->assertEquals(3334, $response->id);
    }

    public function testGetTaxList(): void
    {
        $jsonResponse = <<<JSON
{
    "response": {
        "result": {
            "per_page": 15,
            "total": 2,
            "page": 1,
            "taxes": [
                {
                    "accounting_systemid": "Muu5d",
                    "updated": "2018-07-11 09:30:12",
                    "name": "TAX",
                    "taxid": 3334,
                    "number": "123",
                    "amount": "9.5",
                    "compound": false,
                    "id": 3334
                },
                {
                    "accounting_systemid": "Muu5d",
                    "updated": "2018-07-11 09:30:12",
                    "name": "TBX",
                    "taxid": 3335,
                    "number": null,
                    "amount": "2",
                    "compound": true,
                    "id": 3335
                }
            ],
            "pages": 1
        }
    }
}
JSON;
        $client = $this->preSuccess($jsonResponse);
        $request = (new GetTaxListRequest)
            ->setAccountId("Muu5d");
        $client->getOAuthTestClient()
            ->processRequest($request);

        $response = $request->getResponse();

        $this->assertCount(2, $response);
        $this->assertEquals(15, $response->getMetaData()->per_page);
        $this->assertEquals(2, $response->getMetaData()->total);
        $this->assertEquals(1, $response->getMetaData()->page);
        $this->assertEquals(1, $response->getMetaData()->pages);

        $this->assertInstanceOf(TaxResponse::class, $response[0]);
        $this->assertNull($response[1]->number);
        $this->assertTrue($response[1]->compound);
        $this->assertEquals("TBX", $response[1]->name);
    }
}
