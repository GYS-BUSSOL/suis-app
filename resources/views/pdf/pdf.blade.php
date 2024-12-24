<div id="content">
    <table width="370px" cellpadding="5px" style="border: 1px solid #666666">
        <tr>
            <th colspan="2" align="center">
                <img width="100px" style="padding: 5px;" src="{{ public_path('storage/GYSLogo.png') }}"
                    alt="Garuda Yamato Steel">
            </th>
        </tr>
        <tr>
            <th colspan="2" style="border-top: 1px solid #ccc;"></th>
        </tr>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th colspan="2" align="center">
                <img width="300px" class="img img-responsive" src="{{ public_path('storage/barcode/' . $barcode) }}" />
            </th>
        </tr>
        <tr>
            <td align="center" colspan="4">{{ $visitor_name }}</td>
        </tr>
    </table>
</div>
<div id="editor"></div>
