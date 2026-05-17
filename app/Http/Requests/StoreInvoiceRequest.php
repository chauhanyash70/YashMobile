namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreInvoiceRequest extends FormRequest {
    public function authorize(){ return true; }
    public function rules(){
        return [
           'invoice_no'=>'required|unique:invoices,invoice_no',
           'customer_id'=>'nullable|exists:customers,id',
           'invoice_date'=>'required|date',
           'items'=>'required|array|min:1',
           'items.*.item_type'=>'required|in:device,accessory',
           'items.*.item_id'=>'required|integer',
           'items.*.quantity'=>'required|integer|min:1',
           'items.*.price'=>'required|numeric|min:0'
        ];
    }
}
