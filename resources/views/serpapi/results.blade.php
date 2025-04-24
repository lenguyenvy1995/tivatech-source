



    @if(!empty($results))
            <table class="table table-sm text-center">
                <thead>
                  <tr>
                    <th scope="col">STT</th>
                    <th scope="col">DOMAIN</th>
                    <th scope="col">VỊ TRÍ</th>
                    <th scope="col">TRANG</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach($results->ads as $key=> $result)
                        <tr>
                            <th scope="row" >{{ $key++ }}</th>
                            <td class="text-left"> <a href="{{ $result->link }}">{{ $result->link }}</a></td>
                            <td> {{ $result->position }}</td>
                            <td>@mdo</td>
                        </tr>
                    @endforeach
                
                </tbody>
              </table>
    @else
        <p>No results found.</p>
    @endif
