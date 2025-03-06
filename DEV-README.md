## How It Works
On page load, the script checks for the `av-modal` cookie. If it is unset or false, it triggers a request to [ipify.org](https://www.ipify.org/) to determine the user's IP address.

If that request successfully returns an IP address, it triggers a request to [ipgeolocation.io](https://ipgeolocation.io/) using FSC's API key to determine the state/province associated with the user's IP address.

If that request successfully returns a state in the [`avStates`](https://github.com/freespeechadmin/avActionModal/blob/375bf96c788ac443c183676375362a92327aa05c/avActionModal.js#L9) list, it triggers the creation of the HTML for the modal window and attaches it to the `body` of the page.

# ip_geo_proxy

The `ip_geo_proxy.php` file is used to proxy request through an authorized server to [ipgeolocation.io](https://ipgeolocation.io/). Since some browsers may automatically block frontend requests because of CORS security policies, we need to leverage a backend solution.

## How It Works

You can send a request to our API with a `ip_address` query parameter to determine the state/region of where the provided IP address is likely originating from. For example, running this `curl` command will respond with the following body:

```sh
curl https://api.freespeechcoalition.com/<path>/geoip/?ip_address=84.17.41.190
```

```json
{
    "ip": "84.17.41.190",
    "state_prov": "Washington"
}
```

### Error Responses

#### `ip_address` parameter is missing
If the incoming request is missing the `ip_address` query parameter, the proxy API will respond with an `http status code` of `400` with a `JSON` body of:
```json
{
    "error_code": 4001,
    "error_message": "`ip_address` is required"
}
```

#### `ip_address` parameter is invalid
If the incoming request contains an `ip_address` query parameter but the value is not a valid IP (as defined by the internal [FILTER_VALIDATE_IP](https://www.php.net/manual/en/filter.constants.php#constant.filter-validate-ip) constant), the proxy API will respond with an `http status code` of `400` with a `JSON` body of:
```json
{
    "error_code": 4002,
    "error_message": "`ip_address` is invalid"
}
```

#### Server Side Exception
In the event the proxy encounters an issue, the proxy API will respond with an `http status code` of `500` with a `JSON` body of:
```json
{
    "error_code": 5001,
    "error_message": "<Exception message>"
}
```
