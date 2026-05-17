namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreDeviceRequest extends FormRequest {
    public function authorize(){ return true; }
    public function rules(){
        return [
            'type'=>'required|in:new,old',
            'brand_id'=>'required|exists:brands,id',
            'model_id'=>'required|exists:phone_models,id',
            'storage'=>'nullable|string',
            'ram'=>'nullable|string',
            'color'=>'nullable|string',
            'buy_price'=>'nullable|numeric',
            'sell_price'=>'nullable|numeric',
            'repair_cost'=>'nullable|numeric',
            'stock'=>'nullable|integer'
        ];
    }
}
