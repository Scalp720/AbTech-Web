import React, { useState, useEffect, useRef } from 'react';
import { StyleSheet, Text, View, Button } from 'react-native';
import MapView, { Marker, Polyline } from 'react-native-maps'; 
import * as Location from 'expo-location';
import { FlatList, TouchableOpacity } from 'react-native'; // Import for the list



const USER_ID = 1; 
//const RESPONDER_ID = 12;

const BASE_API_URL = 'http://192.168.10.242/Abtech-Web/API/v1/requests'; // Replace with your actual API URL

export default function App() {
  const [location, setLocation] = useState(null);
  const [errorMsg, setErrorMsg] = useState(null);
  const [responderLocation, setResponderLocation] = useState(null); // Add state for responder location
  const mapRef = useRef(null);
  const [isAlertSent, setIsAlertSent] = useState(false);
  const [role, setRole] = useState('user');
  const [activeAlert, setActiveAlert] = useState(null); // Store the accepted alert
  const [notification, setNotification] = useState(false);
  const notificationListener = useRef();
  const responseListener = useRef();
  


  useEffect(() => {
    (async () => {
      let { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        setErrorMsg('Permission to access location was denied');
        return;
      }

      let userLocation = await Location.getCurrentPositionAsync({});
      setLocation(userLocation);
      if(mapRef.current){
        mapRef.current.animateToRegion({
          latitude: userLocation.coords.latitude,
          longitude: userLocation.coords.longitude,
          latitudeDelta: 0.0922,
          longitudeDelta: 0.0421,
        });
      }
      sendLocationUpdate(USER_ID, userLocation);


      // Start watching for location updates
      Location.watchPositionAsync({
        accuracy: Location.Accuracy.Highest,
        timeInterval: 3000, // Update every 3 seconds (adjust as needed)
        distanceInterval: 1, // Update every 10 meters (adjust as needed)
      }, getLocationUpdate);
      

    })();
  }, []);   
  
  useEffect(() => {
    if (role === 'responder') {
      const eventSource = new EventSource(`${BASE_API_URL}/notification.php?responder_id=${RESPONDER_ID}`);
  
      eventSource.onmessage = (event) => {
        const notification = JSON.parse(event.data);
        // Show alert with notification details
        Alert.alert(
          'New Alert Received',
          `Alert ID: ${notification.alert_id}\nLocation: ${notification.location}\nDescription: ${notification.description}`,
          [
            {
              text: 'Accept',
              onPress: () => acceptAlert(notification.alert_id),
            },
            {
              text: 'Decline',
              onPress: () => console.log('Alert declined'),
              style: 'cancel',
            },
          ],
          { cancelable: false } // Prevent dismissing the alert by tapping outside
        );
      };
  
      // Cleanup function to close EventSource connection
      return () => {
        eventSource.close();
      };
    }
  }, [role]);
  
  // const fetchActiveAlerts = async () => {
  //   try {
  //     const response = await fetch(`${BASE_API_URL}/get_active_alerts.php?responder_id=${RESPONDER_ID}`);
  //     const data = await response.json();
  //     if (data.success) {
  //       setActiveAlerts(data.alerts);
  //     } else {
  //       console.error('Error fetching active alerts:', data.error);
  //     }
  //   } catch (error) {
  //     console.error('Error fetching active alerts:', error);
  //   }
  // };


  const getLocationUpdate = async (newLocation) => {
    setLocation(newLocation); // Update the location state
    sendLocationUpdate(USER_ID, newLocation.coords);

    // Fetch and update responder location
    const fetchedResponderLocation = await fetchResponderLocation(RESPONDER_ID);
    if (fetchedResponderLocation) {
        setResponderLocation(fetchedResponderLocation);
    }
  };

  const fetchResponderLocation = async (responderId) => {
    try {
      const response = await fetch(`${BASE_API_URL}/responder_location.php?user_id=${responderId}`);
      const data = await response.json();
      if (data.success) {
        return data.location; 
      } else {
        console.error('Error fetching responder location:', data.error);
        return null;
      }
    } catch (error) {
      console.error('Error fetching responder location:', error);
      return null;
    }
  };

  // async function registerForPushNotificationsAsync() {
  //   let token;
  //   // ... (your code to get the push notification token)
  //   return token;
  // }
  //*********************************************************************************88 */
  // const sendAlert = async () => {
  //   try {
  //     const response = await fetch(`${BASE_API_URL}/send_alert.php`, {
  //       method: 'POST',
  //       headers: { 'Content-Type': 'application/json' },
  //       body: JSON.stringify({
  //         user_id: USER_ID,
  //         latitude: location.coords.latitude,
  //         longitude: location.coords.longitude,
  //         location: 'balabago', // Example location
  //         description: "Emergency" 
  //       })
  //     });
  
  //     // Check if the response is JSON
  //     const contentType = response.headers.get('content-type');
  //     if (contentType && contentType.indexOf('application/json') !== -1) {
  //       const data = await response.json();
  //       if (data.success) {
  //         setIsAlertSent(true);
  //         alert('Alert sent successfully!');
  //       } else {
  //         alert(`Error sending alert: ${data.error}`);
  //       }
  //       console.log(data);
  //       console.log('Response status:', response.status);
  //       console.log('Response headers:', response.headers);
  //       console.log('Response body:', await response.text());
  //     } else {
  //       const text = await response.text(); // Get the raw text response
  //       console.error('Unexpected response:', text); // Log for debugging
  //       throw new Error('Server returned non-JSON response');
        
  //     }
  
  //   } catch (error) {
  //     console.error('Error:', error.message); // Log the error message
  //     alert('Failed to send alert');
  //   }
  // };

  const sendAlert = async () => {
    try {
      const response = await fetch(`${BASE_API_URL}/send_alert.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: USER_ID,
          latitude: location.coords.latitude,
          longitude: location.coords.longitude,
          location: 'Tagaytay, Calabarzon', 
          description: "Emergency" 
        })
      });

      // Check if the response is JSON
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.indexOf('application/json') !== -1) {
        const data = await response.json(); // Store parsed data in a variable
        if (data.success) {
          setIsAlertSent(true);
          alert('Alert sent successfully!');
        } else {
          alert(`Error sending alert: ${data.error}`);
        }
        console.log('Response data:', data); // Log the data object
      } else {
        const text = await response.text(); 
        console.error('Unexpected response:', text); 
        throw new Error('Server returned non-JSON response');
      }
    } catch (error) {
      console.error('Error:', error.message); 
      alert('Failed to send alert');
    }
  };

//responder
const handleAcceptAlert = (alertId) => {
  acceptAlert(alertId); // Call the existing acceptAlert function
};


const acceptAlert = async (alertId) => {
  try {
    const response = await fetch(`${BASE_API_URL}/accept_alert.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        responder_id: RESPONDER_ID,
        alert_id: alertId,
      })
    });
    const data = await response.json();
    if (data.success) {
      setActiveAlert(alertId); // Set the active alert
      setIsAlertSent(true);
      alert('Alert accepted!');
      // Start sending location updates to the server more frequently
    } else {
      alert('Failed to accept alert: ' + data.error);
    }
  } catch (error) {
    console.error('Error accepting alert:', error);
    alert('Failed to accept alert');
  }
};


  let text = 'Waiting..';
  if (errorMsg) {
    text = errorMsg;
  } else if (location) {
    text = JSON.stringify(location);
  }

  return (
    <View style={styles.container}>
      <MapView 
        ref = {mapRef}
        style={styles.map}
        initialRegion={{
          latitude: location?.coords?.latitude || 14.10, // Default to some location if not available
          longitude: location?.coords?.longitude || 125,
          latitudeDelta: 0.0922,
          longitudeDelta: 0.0421,
        }}
      >
        {location && <Marker coordinate={location.coords} title="Your Location" pinColor={role === 'user' ? 'blue' : 'green'} />}
        {responderLocation && <Marker coordinate={responderLocation} title="Responder Location" pinColor="red" />}
        {isAlertSent && location && responderLocation && (
          <Polyline
            coordinates={[location.coords, responderLocation]}
            strokeColor="red"
            strokeWidth={2}
          />
        )}
      </MapView>

      {/* <Button title="Send Alert" onPress={sendAlert} />
      <Text style={styles.paragraph}>{text}</Text> */}
      {isAlertSent ? (
  <Text>Alert is being processed...</Text> // UI element when alert is active
) : (
  role === 'user' && <Button title="Send Alert" onPress={sendAlert} disabled={!location} />
)}

 {/* Responder UI (Show only if role is 'responder') */}
 {role === 'responder' && (
           <View style={styles.alertsContainer}>
           <Text style={styles.alertsTitle}>Active Alerts:</Text>
           {/* Use FlatList to display the active alerts */}
           <FlatList
             data={activeAlerts}
             keyExtractor={(item) => item.alert_id.toString()}
             renderItem={({ item }) => (
               <TouchableOpacity style={styles.alertItem} onPress={() => handleAcceptAlert(item.alert_id)}>
                 <Text style={styles.alertLocation}>{item.location}</Text>
                 <Text style={styles.alertDescription}>{item.description}</Text>
               </TouchableOpacity>
             )}
           />
         </View>
      )}

      
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
  },
  map: {
    width: '100%',
    height: '50%', // Adjust map height as needed
  },
  paragraph: {
    fontSize: 16,
    textAlign: 'center',
    marginTop: 20,
  },
  alertsContainer: {
    marginTop: 20,
    padding: 10,
  },
  alertsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  alertItem: {
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#ccc',
  },
  alertLocation: {
    fontWeight: 'bold',
  },
  alertDescription: {
    marginTop: 5,
  },
});
