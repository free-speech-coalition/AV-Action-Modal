# AV Action Modal
A modal window that pops up if it detects that a user is located in a state with pending age-verification legislation.


## How To Add It To Your Site

1. Copy this:
`<script src="https://assets.freespeechcoalition.com/code/avActionModal.min.js"></script>`

2. Paste it before the `</body>` tag of your website's code.


## How It Works (Non-Technical)

When a user visits your website, the script checks to see whether they have a cookie indicating that they have already seen the pop up. If not, it checks whether they are  in a state with a pending age-verification bill. If they are, it displays a pop up window asking them to oppose the bill:

![Screenshot of the AV Action Modal on a dark website.](https://assets.freespeechcoalition.com/code/modalDark.png)

Any links clicked will open in a new window/tab. Clicking anywhere on the page (including the links) will close the pop up.

Whether or not the user clicks the links, the cookie is set, ensuring that they do not see the message again.



## FAQs
### Why did FSC create this?
Politicians' number one priority is reelection, so they care far more about what the voters in their district think than what the porn industry thinks. We were able to defeat Arizona's law in 2024 by mobilizing constituents to pressure the governor into vetoing a bill that the legislature had passed. We think we can do it again this year in more states.

### Which states will see the pop up?
Arizona, Hawaii, Illinois, Iowa, Maryland, Missouri, New Mexico, New York, North Dakota, Ohio, Oregon, South Dakota, West Virginia, and Wyoming.

### What happens when the user clicks the link to contact their representatives?
They are taken to a form where they can send a message to their elected officials. (Example: [Illinois](https://www.defendonlineprivacy.com/il/action.php))

### What if the user isn't in the state associated with their IP address?
Under the button urging them to take action, we link to a [page](https://defendonlineprivacy.com/geolocation.php) explaining that geotargeting is imperfect and that they could still be affected by the law even if they don't live in that state.

### Do I have to pay to use this?
No. FSC is providing access to this code (including the geolocation service) free of charge so that websites can mobilize their customers to stop the spread of bad age-verification laws. In the event that the number of API requests surpasses FSC's budget, the pop up will simply not appear.

### What if I have a problem adding the code or the pop up looks wrong?
Let Alison know!


## How It Works (Slightly Technical)
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
