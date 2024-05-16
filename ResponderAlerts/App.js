import React, { useState, useEffect, useRef } from 'react';
import { StyleSheet, Text, View, Button } from 'react-native';
import MapView, { Marker } from 'react-native-maps'; 
import * as Location from 'expo-location';

const USER_ID = 1; 
const RESPONDER_ID = 12;

const BASE_API_URL = 'http://localhost/AbTech-Web/API/v1/request'; // Replace with your actual API URL

export default function App() {
  const [location, setLocation] = useState(null);
  const [errorMsg, setErrorMsg] = useState(null);
  const [responderLocation, setResponderLocation] = useState(null); // Add state for responder location
  const mapRef = useRef(null);

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
      const response = await fetch(`${BASE_API_URL}/get_responder_location.php?user_id=${responderId}`);
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
  //*********************************************************************************88 */
  const sendAlert = async () => {
    try {
      const response = await fetch(`${BASE_API_URL}/send_alert.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: USER_ID,
          latitude: location.coords.latitude,
          longitude: location.coords.longitude,
          location: "123 Main Street, Anytown", // Example location
          description: "Emergency" 
        })
      });
  
      // Check if the response is JSON
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.indexOf('application/json') !== -1) {
        const data = await response.json();
        if (data.success) {
          setIsAlertSent(true);
          alert('Alert sent successfully!');
        } else {
          alert(`Error sending alert: ${data.error}`);
        }
        console.log(data);
      } else {
        const text = await response.text(); // Get the raw text response
        console.error('Unexpected response:', text); // Log for debugging
        throw new Error('Server returned non-JSON response');
      }
  
    } catch (error) {
      console.error('Error:', error.message); // Log the error message
      alert('Failed to send alert');
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
        {location && <Marker coordinate={location.coords} title="Your Location" />}
        {responderLocation && <Marker coordinate={responderLocation} title="Responder Location" pinColor="green" />}
      </MapView>

      <Button title="Send Alert" onPress={sendAlert} />
      <Text style={styles.paragraph}>{text}</Text>
      
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
});
